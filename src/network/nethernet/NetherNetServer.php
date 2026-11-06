<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

use RuntimeException;
use Socket;
use Throwable;
use watermossmc\network\mcpe\PacketDispatcher;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\nethernet\webrtc\DtlsIdentity;
use watermossmc\network\nethernet\webrtc\LocalIdentity;
use watermossmc\network\nethernet\webrtc\PeerConnection;
use watermossmc\network\nethernet\webrtc\Sdp;
use watermossmc\network\nethernet\webrtc\Stun;
use watermossmc\network\Session;
use watermossmc\player\PlayerManager;
use watermossmc\Server;
use watermossmc\util\Config;
use watermossmc\util\Logger;

final class NetherNetServer
{
    private Socket $iceSocket;

    private ?Socket $discoverySocket = null;

    private readonly DtlsIdentity $identity;

    private readonly LocalIdentity $localIdentity;

    private readonly string $networkId;

    /** @var array<string, PeerConnection> */
    private array $byUfrag = [];

    /** @var array<string, PeerConnection> */
    private array $byAddr = [];

    /** @var array<string, PeerConnection> */
    private array $byConnection = [];

    /** @var array<string, Session> */
    private array $sessions = [];

    /** @var array<string, string> connectionId => remote IP seen at signaling time */
    private array $connIp = [];

    private readonly RateLimiter $packetLimiter;

    private readonly RateLimiter $connectLimiter;

    private readonly int $maxConnectionsPerIp;

    private float $nextPrune = 0.0;

    public function __construct(
        private readonly Server $server,
        private readonly string $bindIp,
        private readonly int $icePort,
        private readonly string $publicIp = '',
        bool $lanDiscovery = true
    ) {
        $this->identity = DtlsIdentity::generate();
        $this->localIdentity = LocalIdentity::generate($this->identity->opensslOptions);
        $this->networkId = Uint64::random();
        $this->packetLimiter = new RateLimiter(Config::getInt('nethernet-max-packets-per-second-per-ip', 2000), 1.0);
        $this->connectLimiter = new RateLimiter(Config::getInt('nethernet-max-connect-requests-per-10s-per-ip', 5), 10.0);
        $this->maxConnectionsPerIp = Config::getInt('nethernet-max-connections-per-ip', 5);

        $sock = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        if ($sock === false) {
            throw new RuntimeException('NetherNet: socket_create failed: ' . socket_strerror(socket_last_error()));
        }
        socket_set_option($sock, \SOL_SOCKET, \SO_REUSEADDR, 1);
        if (!socket_bind($sock, $bindIp, $icePort)) {
            throw new RuntimeException("NetherNet: cannot bind {$bindIp}:{$icePort}: " . socket_strerror(socket_last_error($sock)));
        }
        socket_set_nonblock($sock);
        $this->iceSocket = $sock;

        if ($lanDiscovery) {
            $d = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
            if ($d !== false) {
                socket_set_option($d, \SOL_SOCKET, \SO_REUSEADDR, 1);
                socket_set_option($d, \SOL_SOCKET, \SO_BROADCAST, 1);
                if (@socket_bind($d, '0.0.0.0', DiscoveryCodec::PORT)) {
                    socket_set_nonblock($d);
                    $this->discoverySocket = $d;
                } else {
                    Logger::warning('[NetherNet] LAN discovery disabled: cannot bind UDP ' . DiscoveryCodec::PORT);
                }
            }
        }
        Logger::info("[NetherNet] Listening ICE/DTLS on {$bindIp}:{$icePort} | networkId={$this->networkId} | cert=" . $this->identity->fingerprint);
    }

    public function getNetworkId(): string
    {
        return $this->networkId;
    }

    public function poll(): void
    {
        $buf = '';
        $ip = '';
        $port = 0;
        for ($i = 0; $i < 512 && @socket_recvfrom($this->iceSocket, $buf, 65535, \MSG_DONTWAIT, $ip, $port) !== false; $i++) {
            try {
                $this->handleIce($buf, $ip, $port);
            } catch (Throwable $e) {
                Logger::error("[NetherNet] ICE packet error from {$ip}:{$port}: " . $e->getMessage());
            }
        }
        if ($this->discoverySocket !== null) {
            for ($i = 0; $i < 64 && @socket_recvfrom($this->discoverySocket, $buf, 65535, \MSG_DONTWAIT, $ip, $port) !== false; $i++) {
                try {
                    $this->handleDiscovery($buf, $ip, $port);
                } catch (Throwable $e) {
                    Logger::error("[NetherNet] Discovery packet error from {$ip}:{$port}: " . $e->getMessage());
                }
            }
        }
    }

    public function tick(): void
    {
        $now = microtime(true);
        if ($now >= $this->nextPrune) {
            $this->nextPrune = $now + 10.0;
            $this->packetLimiter->prune($now);
            $this->connectLimiter->prune($now);
        }
        foreach ($this->byConnection as $peer) {
            try {
                $peer->tick($now);
            } catch (Throwable $e) {
                Logger::error('[NetherNet] Peer tick error: ' . $e->getMessage());
                $peer->close('internal error');
            }
        }
    }

    public function shutdown(): void
    {
        foreach ($this->byConnection as $peer) {
            $peer->close('server shutdown');
        }
    }

    /**
     * Signaling entry point (LAN or any other source).
     *
     * @param callable(Signal): void $reply
     */
    public function handleSignal(Signal $signal, string $remoteIp, callable $reply): void
    {
        switch ($signal->type) {
            case Signal::CONNECT_REQUEST:
                $this->onConnectRequest($signal, $remoteIp, $reply);
                break;
            case Signal::CONNECT_ERROR:
                ($this->byConnection[$signal->connectionId] ?? null)?->close('remote error ' . $signal->data);
                break;
            default:
                // CANDIDATEADD is ignored: we are ICE-lite and learn the peer address from STUN.
                break;
        }
    }

    private function handleIce(string $d, string $ip, int $port): void
    {
        if ($d === '') {
            return;
        }
        $verdict = $this->packetLimiter->check($ip, microtime(true));
        if ($verdict !== RateLimiter::ALLOW) {
            if ($verdict === RateLimiter::DROP_FIRST) {
                Logger::warning("[NetherNet] Packet rate limit exceeded by {$ip}, dropping excess packets");
            }
            return;
        }
        if (Stun::looksLikeStun($d)) {
            $st = Stun::parse($d);

            if ($st === null || $st['type'] !== Stun::BINDING_REQUEST) {
              Logger::debug("[NetherNet] Invalid STUN packet from {$ip}:{$port}");
              return;
            }

            $ufrag = explode(':', $st['attrs'][Stun::ATTR_USERNAME] ?? '', 2)[0];
            $peer = $this->byUfrag[$ufrag] ?? null;

            if ($peer === null) {
                Logger::debug("[NetherNet] STUN unknown ufrag '{$ufrag}' from {$ip}:{$port}");
                return;
            }

            if (!$peer->handleStun($d, $st, $ip, $port)) {
                Logger::debug("[NetherNet] STUN integrity failed from {$ip}:{$port}");
                return;
            }

            $this->byAddr["{$ip}:{$port}"] = $peer;

            Logger::debug("[NetherNet] STUN validated {$ip}:{$port}");
    
            return;
        }
        $peer = $this->byAddr["{$ip}:{$port}"] ?? null;

        if ($peer === null) {
            Logger::debug(
                "[NetherNet] Unknown UDP endpoint {$ip}:{$port}, size=" . strlen($d)
            );
            return;
        }

        $contentType = ord($d[0]);

        if ($contentType >= 20 && $contentType <= 63) {
            Logger::debug(sprintf('[NetherNet] DTLS packet from %s:%d | type=0x%02X | size=%d', $ip, $port, $contentType, strlen($d)));

            $peer->handleDtls($d, $ip, $port);
            return;
        }

        Logger::debug(sprintf(
            '[NetherNet] Unknown packet from %s:%d | type=0x%02X | size=%d',
            $ip,
            $port,
            $contentType,
            strlen($d)
        ));
    }

    private function sendUdp(string $ip, int $port, string $data): void
    {
        @socket_sendto($this->iceSocket, $data, \strlen($data), 0, $ip, $port);
    }

    /** @param callable(Signal): void $reply */
    private function onConnectRequest(Signal $signal, string $remoteIp, callable $reply): void
    {
        $conn = $signal->connectionId;
        if (isset($this->byConnection[$conn])) {
            return;
        }
        $verdict = $this->connectLimiter->check($remoteIp, microtime(true));
        if ($verdict !== RateLimiter::ALLOW) {
            if ($verdict === RateLimiter::DROP_FIRST) {
                Logger::warning("[NetherNet] CONNECTREQUEST rate limit exceeded by {$remoteIp}, ignoring further requests");
            }
            return;
        }
        if ($this->maxConnectionsPerIp > 0 && \count(array_keys($this->connIp, $remoteIp, true)) >= $this->maxConnectionsPerIp) {
            Logger::warning("[NetherNet] Rejected {$remoteIp}: already has {$this->maxConnectionsPerIp} connections");
            $reply(new Signal(Signal::CONNECT_ERROR, $conn, (string) Signal::ERROR_INTERNAL));
            return;
        }
        Logger::debug("[NetherNet] SDP OFFER:\n" . $signal->data);
        $offer = Sdp::parseOffer($signal->data);
        if ($offer === null || \count($this->byConnection) >= max(8, $this->server->getMaxPlayers() * 2)) {
            $reply(new Signal(Signal::CONNECT_ERROR, $conn, (string) Signal::ERROR_INTERNAL));
            return;
        }
        $peer = new PeerConnection(
            $conn,
            $offer,
            $this->identity,
            fn (string $ip, int $port, string $data) => $this->sendUdp($ip, $port, $data),
            fn (PeerConnection $p) => $this->onPeerOpen($p),
            fn (PeerConnection $p, string $msg) => $this->onPeerMessage($p, $msg),
            fn (PeerConnection $p, string $why) => $this->onPeerClosed($p, $why)
        );
        $this->byUfrag[$peer->ufrag] = $peer;
        $this->byConnection[$conn] = $peer;
        $this->connIp[$conn] = $remoteIp;

        $serverIp = $this->localIpFor($remoteIp);

        $answer = Sdp::buildAnswer(
            $offer['mid'],
            $peer->ufrag,
            $peer->pwd,
            $this->identity->fingerprint,
            $this->localIdentity->issue($this->identity->fingerprint),
            $serverIp,
            $this->icePort,
            $offer['sctpPort']
        );
        Logger::debug("[NetherNet] SDP ANSWER:\n" . $answer);
        Logger::debug("[NetherNet] CONNECTREQUEST conn={$conn} from {$remoteIp}, candidate={$serverIp}:{$this->icePort}");
        $reply(new Signal(Signal::CONNECT_RESPONSE, $conn, $answer));
    }

    private function localIpFor(string $remoteIp): string
    {
        if ($this->publicIp !== '') {
            return $this->publicIp;
        }
        if ($this->bindIp !== '0.0.0.0' && $this->bindIp !== '') {
            return $this->bindIp;
        }
        $s = @socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        if ($s !== false) {
            $addr = '';
            $port = 0;
            if (@socket_connect($s, $remoteIp, 9) && @socket_getsockname($s, $addr, $port) && $addr !== '' && $addr !== '0.0.0.0') {
                socket_close($s);
                return $addr;
            }
            socket_close($s);
        }
        return '127.0.0.1';
    }

    private function handleDiscovery(string $d, string $ip, int $port): void
    {
        $pk = DiscoveryCodec::decode($d);
        if ($pk === null || $pk['sender'] === $this->networkId) {
            return;
        }
        switch ($pk['id']) {
            case DiscoveryCodec::PACKET_REQUEST:
                $info = DiscoveryCodec::encodeServerInfo(
                    $this->server->getMotd(),
                    ProtocolInfo::CURRENT_PROTOCOL,
                    ProtocolInfo::MINECRAFT_VERSION_NETWORK,
                    Config::getString('level-name', 'world'),
                    $this->gameTypeId(),
                    $this->server->getOnlinePlayerCount(),
                    $this->server->getMaxPlayers(),
                    Config::getBool('online-mode', true),
                    true
                );
                $this->sendDiscovery(DiscoveryCodec::PACKET_RESPONSE, DiscoveryCodec::encodeResponseBody($info), $ip, $port);
                break;
            case DiscoveryCodec::PACKET_MESSAGE:
                $msg = DiscoveryCodec::decodeMessageBody($pk['body']);
                if ($msg === null || $msg['recipient'] !== $this->networkId || $msg['signal'] === '' || $msg['signal'] === 'Ping') {
                    return;
                }
                $signal = Signal::decode($msg['signal']);
                if ($signal === null) {
                    return;
                }
                $sender = $pk['sender'];
                $this->handleSignal($signal, $ip, function (Signal $out) use ($sender, $ip, $port): void {
                    $this->sendDiscovery(
                        DiscoveryCodec::PACKET_MESSAGE,
                        DiscoveryCodec::encodeMessageBody($sender, $out->encode()),
                        $ip,
                        $port
                    );
                });
                break;
        }
    }

    private function sendDiscovery(int $id, string $body, string $ip, int $port): void
    {
        if ($this->discoverySocket === null) {
            return;
        }
        $pk = DiscoveryCodec::encode($id, $this->networkId, $body);
        @socket_sendto($this->discoverySocket, $pk, \strlen($pk), 0, $ip, $port);
    }

    private function gameTypeId(): int
    {
        return match (strtolower(Config::getString('gamemode', 'survival'))) {
            'creative' => 1,
            'adventure' => 2,
            default => 0,
        };
    }

    private function onPeerOpen(PeerConnection $peer): void
    {
        $session = new Session((string) $peer->remoteIp, $peer->remotePort);
        $session->attachSocket($this->iceSocket);
        $session->attachTransport(new NetherNetTransport($peer));
        $session->setRakNetState(Session::RN_CONNECTED);
        $this->sessions[$peer->ufrag] = $session;
        Logger::info("[NetherNet] Connection open: {$peer->remoteIp}:{$peer->remotePort} (conn {$peer->connectionId})");
    }

    private function onPeerMessage(PeerConnection $peer, string $msg): void
    {
        $session = $this->sessions[$peer->ufrag] ?? null;
        if ($session === null || $msg === '') {
            return;
        }
        $session->touch();
        try {
            // NetherNet data channels carry raw MCPE batches directly, with no
            // RakNet-style leading packet ID (unlike the 0xFE game-packet ID in RakNet).
            PacketDispatcher::handleBatch($msg, $session, $this->iceSocket);
        } catch (Throwable $e) {
            Logger::error('[NetherNet] Packet handling error: ' . $e->getMessage());
        }
    }

    private function onPeerClosed(PeerConnection $peer, string $why): void
    {
        Logger::info("[NetherNet] Connection closed (conn {$peer->connectionId}): {$why}");
        $session = $this->sessions[$peer->ufrag] ?? null;
        unset($this->sessions[$peer->ufrag], $this->byUfrag[$peer->ufrag], $this->byConnection[$peer->connectionId], $this->connIp[$peer->connectionId]);
        foreach ($this->byAddr as $key => $p) {
            if ($p === $peer) {
                unset($this->byAddr[$key]);
            }
        }
        if ($session !== null) {
            try {
                PlayerManager::remove($session);
            } finally {
                $session->close(false);
            }
        }
    }
}
