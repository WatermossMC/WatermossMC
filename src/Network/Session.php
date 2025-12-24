<?php

declare(strict_types=1);

namespace WatermossMC\Network;

use Socket;
use WatermossMC\Crypto\EncryptionContext;
use WatermossMC\Minecraft\Packets\NetworkSettings;

final class Session
{
    /* RakNet */
    public int $sendSequence = 0;

    public int $lastReceivedSequence = -1;

    public int $frameSeq = 0;

    public int $reliableSeq = 0;

    /** @var array<int, int> */
    public array $orderedSeq = [];

    /** @var array<int, string> */
    public array $reliableQueue = [];

    /** @var array<int, string> */
    public array $outgoingFrames = [];

    /** @var array<int, bool> */
    private array $received = [];

    /* Network */
    public string $address;

    public int $port;

    public int $mtu = 1492;

    public int $lastSeen;

    /* Login */
    public int $guid = 0;

    private string $uuid = '';

    private string $username = '';

    private ?string $xuid = null;

    private ?string $handshakeJwt = null;

    private bool $compressOutbound = false;

    private bool $compressInbound = false;

    private int $compressionAlgorithm = NetworkSettings::COMPRESS_NOTHING;

    private bool $networkSettingsSent = false;

    private bool $compressionEnabled = false;

    private ?string $clientPublicKey = null;

    private ?array $serverKeys = null;

    private ?EncryptionContext $encryption = null;

    private bool $handshakeDone = false;

    private ?string $pendingKey = null;

    private ?string $pendingIv = null;

    private bool $hasWaitingHandshakeAck = false;

    private float $x = 0.0;

    private float $y = 0.0;

    private float $z = 0.0;

    /* Runtime */
    private int $runtimeId;

    public const RN_CONNECTING = 0;
    public const RN_CONNECTED = 1;
    public const RN_DISCONNECTING = 2;
    public const RN_DISCONNECTED = 3;

    private int $raknetState = self::RN_CONNECTING;

    public const MC_NONE = 0;
    public const MC_NETWORK = 1;
    public const MC_HANDSHAKE = 2;
    public const MC_LOGIN = 3;
    public const MC_RESOURCE = 4;
    public const MC_PLAY = 5;

    private int $mcpeState = self::MC_NONE;

    private static int $nextRuntimeId = 1;

    private ?Socket $socket = null;

    public function __construct(string $addr, int $port)
    {
        $this->address = $addr;
        $this->port = $port;
        $this->runtimeId = self::$nextRuntimeId++;
        $this->lastSeen = time();
    }

    public function nextSendSeq(): int
    {
        return $this->sendSequence++;
    }

    public function nextReliableSeq(): int
    {
        return $this->reliableSeq++;
    }

    public function markReceived(int $seq): bool
    {
        if (isset($this->received[$seq])) {
            return false;
        }

        $this->received[$seq] = true;
        if (\count($this->received) > 4096) {
            array_shift($this->received);
        }
        return true;
    }

    public function storeReliable(int $seq, string $frame): void
    {
        $this->outgoingFrames[$seq] = $frame;
    }

    /* Login */
    public function setLoginData(string $uuid, string $username, ?string $xuid): void
    {
        $this->uuid = $uuid;
        $this->username = $username;
        $this->xuid = $xuid;
        $this->mcpeState = self::MC_LOGIN;
    }

    public function setHandshakeJwt(string $jwt): void
    {
        $this->handshakeJwt = $jwt;
    }

    public function getHandshakeJwt(): string
    {
        if ($this->handshakeJwt === null) {
            throw new \LogicException("Handshake JWT not set");
        }
        return $this->handshakeJwt;
    }

    public function enterPlay(): void
    {
        $this->mcpeState = self::MC_PLAY;
    }

    public function getRakNetState(): int
    {
        return $this->raknetState;
    }

    public function setRakNetState(int $raknetState): void
    {
        $this->raknetState = $raknetState;
    }

    public function isRakNetConnected(): bool
    {
        return $this->raknetState === self::RN_CONNECTED;
    }

    public function getMcpeState(): int
    {
        return $this->mcpeState;
    }

    public function setMcpeState(int $mcpeState): void
    {
        $this->mcpeState = $mcpeState;
    }

    public function isPlaying(): bool
    {
        return $this->mcpeState === self::MC_PLAY;
    }

    public function setPosition(float $x, float $y, float $z): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * @return array{x:float, y:float, z:float}
     */
    public function getPosition(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getXuid(): ?string
    {
        return $this->xuid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRuntimeId(): int
    {
        return $this->runtimeId;
    }

    public function getGameMode(): int
    {
        return 0; // survival
    }

    /**
     * @return array{yaw: float, pitch: float}
     */
    public function getRotation(): array
    {
        return [
            'yaw' => 0.0,
            'pitch' => 0.0,
        ];
    }

    public function enableOutboundCompression(int $algo): void
    {
        $this->setCompressionEnabled();
        $this->compressOutbound = true;
        $this->compressionAlgorithm = $algo;
    }

    public function enableInboundCompression(): void
    {
        $this->setCompressionEnabled();
        $this->compressInbound = true;
    }

    public function shouldCompressOutbound(): bool
    {
        return $this->compressOutbound;
    }

    public function shouldDecompressInbound(): bool
    {
        return $this->compressInbound;
    }

    public function getCompressionAlgorithm(): int
    {
        return $this->compressionAlgorithm;
    }

    public function attachSocket(Socket $sock): void
    {
        $this->socket = $sock;
    }

    public function assertOpen(): bool
    {
        return $this->raknetState === self::RN_CONNECTED;
    }

    public function close(bool $notify = true): void
    {
        if ($this->raknetState === self::RN_DISCONNECTED) {
            return;
        }

        $this->raknetState = self::RN_DISCONNECTING;

        $this->reliableQueue = [];
        $this->outgoingFrames = [];
        $this->orderedSeq = [];

        if ($notify && $this->socket !== null) {
            $pk = "\x15"; // DISCONNECT_NOTIFICATION

            @socket_sendto(
                $this->socket,
                $pk,
                1,
                0,
                $this->address,
                $this->port
            );
        }

        $this->raknetState = self::RN_DISCONNECTED;
    }

    public function isClosed(): bool
    {
        return $this->raknetState === self::RN_DISCONNECTED;
    }

    public function setClientPublicKey(string $pem): void
    {
        $this->clientPublicKey = $pem;
    }

    public function getClientPublicKey(): string
    {
        return $this->clientPublicKey
            ?? throw new \RuntimeException("Client public key not set");
    }

    public function setServerKeys(array $keys): void
    {
        $this->serverKeys = $keys;
    }

    public function enableEncryption(string $key, string $iv): void
    {
        $this->encryption = new EncryptionContext($key, $iv);
    }

    public function hasEncryption(): bool
    {
        return $this->encryption !== null;
    }

    public function encrypt(string $data): string
    {
        return $this->encryption?->encrypt($data) ?? $data;
    }

    public function decrypt(string $data): string
    {
        return $this->encryption?->decrypt($data) ?? $data;
    }

    public function setHandshakeDone(): void
    {
        $this->handshakeDone = true;
    }

    public function isHandshakeDone(): bool
    {
        return $this->handshakeDone;
    }

    public function setCompressionEnabled(): void
    {
        $this->compressionEnabled = true;
    }

    public function setPendingEncryption(string $key, string $iv): void
    {
        $this->pendingKey = $key;
        $this->pendingIv = $iv;
    }

    public function hasPendingEncryption(): bool
    {
        return $this->pendingKey !== null;
    }

    public function enablePendingEncryption(): void
    {
        $this->enableEncryption($this->pendingKey, $this->pendingIv);
        $this->pendingKey = null;
        $this->pendingIv = null;
    }

    public function markNetworkSettingsSent(): void
    {
        $this->networkSettingsSent = true;
    }

    public function hasSentNetworkSettings(): bool
    {
        return $this->networkSettingsSent;
    }

    public function isCompressionEnabled(): bool
    {
        return $this->compressionEnabled;
    }

    public function hasWaitingHandshakeAck(): bool
    {
        return $this->hasWaitingHandshakeAck;
    }

    public function setWaitingHandshakeAck(bool $v): void
    {
        $this->hasWaitingHandshakeAck = $v;
    }
}
