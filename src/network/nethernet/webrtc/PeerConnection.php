<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

use Closure;
use watermossmc\network\nethernet\MessageReassembler;
use watermossmc\network\nethernet\NetherNetException;
use watermossmc\util\Logger;

final class PeerConnection
{
    public const LABEL_RELIABLE = 'ReliableDataChannel';

    public const LABEL_UNRELIABLE = 'UnreliableDataChannel';

    private const HANDSHAKE_TIMEOUT = 30.0;
    private const IDLE_TIMEOUT = 30.0;
    /** Max seconds between DTLS being established and both data channels opening. */
    private const CHANNEL_TIMEOUT = 5.0;
    /** Max seconds to flush queued data (e.g. the disconnect packet) before a graceful close is forced. */
    private const DISCONNECT_FLUSH_TIMEOUT = 2.0;

    public readonly string $ufrag;

    public readonly string $pwd;

    public ?string $remoteIp = null;

    public int $remotePort = 0;

    private readonly DtlsServer $dtls;

    private readonly SctpAssociation $sctp;

    /** @var array<string, true> */
    private array $validated = [];

    /** @var array<int, MessageReassembler> */
    private array $reassemblers = [];

    private ?int $reliableStream = null;
    private ?int $unreliableStream = null;

    private bool $open = false;

    private bool $closed = false;

    private readonly float $createdAt;

    private float $lastActivity;
    private ?float $dtlsReadyAt = null;
    private ?float $disconnectDeadline = null;
    private string $disconnectReason = '';

    /**
     * @param array{ufrag: string, pwd: string, fingerprint: string, mid: string, sctpPort: int} $offer
     * @param Closure(string, int, string): void $udpSend  (ip, port, data)
     * @param Closure(self): void                $onOpen
     * @param Closure(self, string): void        $onMessage complete NetherNet message (after reassembly)
     * @param Closure(self, string): void        $onClosed
     */
    public function __construct(
        public readonly string $connectionId,
        private readonly array $offer,
        DtlsIdentity $identity,
        private readonly Closure $udpSend,
        private readonly Closure $onOpen,
        private readonly Closure $onMessage,
        private readonly Closure $onClosed
    ) {
        $this->ufrag = bin2hex(random_bytes(4));
        $this->pwd = bin2hex(random_bytes(12));
        $this->createdAt = $this->lastActivity = microtime(true);

        $this->sctp = new SctpAssociation(
            function (string $pkt): void {
                $this->dtls->send($pkt);
            },
            function (int $sid, int $ppid, string $data): void {
                $this->onSctpMessage($sid, $data);
            },
            function (int $sid, string $label): void {
                $this->onChannelOpen($sid, $label);
            },
            function (string $why): void {
                $this->close($why);
            },
            5000,
            $offer['sctpPort']
        );

        $this->dtls = new DtlsServer(
            $identity,
            $offer['fingerprint'],
            function (string $d): void {
                if ($this->remoteIp !== null) {
                    ($this->udpSend)($this->remoteIp, $this->remotePort, $d);
                }
            },
            function (string $data): void {
                $this->lastActivity = microtime(true);
                $firstByte = $data === '' ? 0 : ord($data[0]);

                Logger::debug(sprintf(
                  '[NetherNet] DTLS application data received | size=%d | firstByte=0x%02X',
                  strlen($data),
                  $firstByte
                ));
                $this->sctp->receive($data);
            },
            function (string $why): void {
                $this->close($why);
            },
            function (): void {
                $this->dtlsReadyAt = microtime(true);
                $this->sctp->initiate();
            }
        );
    }

    public function getOffer(): array
    {
        return $this->offer;
    }

    public function isOpen(): bool
    {
        return $this->open && !$this->closed;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    /** Returns true if the Binding Request is valid (peer address accepted). */
    public function handleStun(string $data, array $parsed, string $ip, int $port): bool
    {
        if ($this->closed || !Stun::verifyIntegrity($data, $parsed, $this->pwd)) {
            return false;
        }
        $resp = Stun::buildSuccess($parsed['txid'], $ip, $port, $this->pwd);
        if ($resp === null) {
            return false;
        }
        $this->validated["{$ip}:{$port}"] = true;
        $this->remoteIp = $ip;
        $this->remotePort = $port;
        $this->lastActivity = microtime(true);
        ($this->udpSend)($ip, $port, $resp);
        return true;
    }

    public function handleDtls(string $data, string $ip, int $port): void
    {
        if ($this->closed || !isset($this->validated["{$ip}:{$port}"])) {
            return;
        }
        $this->lastActivity = microtime(true);
        $this->dtls->receive($data);
    }

    /** Sends one NetherNet segment over the ReliableDataChannel. */
    public function sendReliable(string $segment): bool
    {
        if (!$this->isOpen() || $this->reliableStream === null) {
            return false;
        }
        return $this->sctp->sendMessage($this->reliableStream, SctpAssociation::PPID_BINARY, $segment);
    }

    public function sendUnreliable(string $segment): bool
    {
        if (!$this->isOpen() || $this->unreliableStream === null) {
            return false;
        }

        if (!$this->sctp->isChannelUnreliable($this->unreliableStream)) {
            return false;
        }

        return $this->sctp->sendUnreliableMessage(
            $this->unreliableStream,
            SctpAssociation::PPID_BINARY,
            $segment
        );
    }

    public function flush(): void
    {
        if (!$this->closed) {
            $this->sctp->flush();
        }
    }

    public function tick(float $now): void
    {
        if ($this->closed) {
            return;
        }
        $this->dtls->tick($now);
        $this->sctp->tick($now);
        if ($this->disconnectDeadline !== null) {
            if (!$this->sctp->hasUnsettledData()) {
                $this->close($this->disconnectReason);
            } elseif ($now >= $this->disconnectDeadline) {
                Logger::warning("[NetherNet] Flush timeout on graceful close (conn {$this->connectionId}), dropping unsent data: {$this->disconnectReason}");
                $this->close($this->disconnectReason);
            }
            return;
        }
        if (!$this->open && $now - $this->createdAt > self::HANDSHAKE_TIMEOUT) {
            $this->close('WebRTC handshake timeout');
        } elseif (!$this->open && $this->dtlsReadyAt !== null && $now - $this->dtlsReadyAt > self::CHANNEL_TIMEOUT) {
            $this->close('data channels did not open within ' . self::CHANNEL_TIMEOUT . 's after DTLS');
        } elseif ($now - $this->lastActivity > self::IDLE_TIMEOUT) {
            $this->close('WebRTC idle timeout');
        }
    }

    /**
     * Closes after queued data is delivered, or after DISCONNECT_FLUSH_TIMEOUT at the latest.
     * Falls back to an immediate close when there is nothing to flush or the channel never opened.
     */
    public function closeGracefully(string $why): void
    {
        if ($this->closed || $this->disconnectDeadline !== null) {
            return;
        }
        if (!$this->open) {
            $this->close($why);
            return;
        }
        $this->disconnectReason = $why;
        $this->disconnectDeadline = microtime(true) + self::DISCONNECT_FLUSH_TIMEOUT;
        $this->sctp->flush();
        if (!$this->sctp->hasUnsettledData()) {
            $this->close($why);
        }
    }

    public function close(string $why): void
    {
        if ($this->closed) {
            return;
        }
        $this->closed = true;
        $this->sctp->close();
        $this->dtls->close();
        ($this->onClosed)($this, $why);
    }

    private function onChannelOpen(int $sid, string $label): void
    {
        Logger::debug("[NetherNet] data channel '{$label}' opened on stream {$sid} (conn {$this->connectionId})");
        if ($label === self::LABEL_RELIABLE) {
            $this->reliableStream = $sid;
        } elseif ($label === self::LABEL_UNRELIABLE) {
            $this->unreliableStream = $sid;
        } else {
            Logger::debug("[NetherNet] unexpected data channel '{$label}'");
            return;
        }

        if (!$this->open && $this->reliableStream !== null) {
            $this->open = true;
            ($this->onOpen)($this);
        }
    }

    private function onSctpMessage(int $sid, string $data): void
    {
        $this->lastActivity = microtime(true);
        $label = $this->sctp->getChannelLabel($sid);
        if ($label !== self::LABEL_RELIABLE && $label !== self::LABEL_UNRELIABLE) {
            return;
        }
        $fragmentationAllowed = ($label === self::LABEL_RELIABLE);
        try {
            $msg = ($this->reassemblers[$sid] ??= new MessageReassembler($fragmentationAllowed))->push($data);
        } catch (NetherNetException $e) {
            // Malformed framing means the peer is broken or hostile; dropping it silently would hide the cause.
            Logger::warning("[NetherNet] Bad framing on '{$label}' (conn {$this->connectionId}): " . $e->getMessage());
            $this->close('bad framing: ' . $e->getMessage());
            return;
        }
        if ($msg !== null && $this->open) {
            ($this->onMessage)($this, $msg);
        }
    }
}
