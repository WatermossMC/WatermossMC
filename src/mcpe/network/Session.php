<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare (strict_types=1);

namespace watermossmc\mcpe\network;

use function count;

use LogicException;
use RuntimeException;
use Socket;
use watermossmc\crypto\EncryptionContext;
use watermossmc\mcpe\protocol\NetworkSettings;
use watermossmc\util\Logger;

final class Session
{
    public const RN_CONNECTING = 0;
    public const RN_CONNECTED = 1;
    public const RN_DISCONNECTING = 2;
    public const RN_DISCONNECTED = 3;
    public const MC_NONE = 0;
    public const MC_NETWORK = 1;
    public const MC_HANDSHAKE = 2;
    public const MC_LOGIN = 3;
    public const MC_RESOURCE = 4;
    public const MC_PRESPAWN = 5;
    public const MC_PLAY = 6;

    public int $sendSequence = 0;

    public int $orderedIndex = 0;

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

    /** @var array<int, array{count?: int, parts: array<int, string>}> */
    public array $fragments = [];

    /** @var array<int, array<int, string|null>> */
    public array $splitQueue = [];

    /** @var array<int, string> */
    public array $sendQueue = [];

    public string $address;

    public int $port;

    public int $mtu = 1492;

    public int $lastSeen;

    public int $guid = 0;

    private string $uuid = '';

    private string $username = '';

    private ?string $xuid = null;

    private ?string $handshakeJwt = null;

    private bool $compressOutbound = false;

    private bool $compressInbound = false;

    private int $compressionAlgorithm = NetworkSettings::COMPRESS_NOTHING;

    private ?int $networkSettingsReliableSeq = null;

    private bool $networkSettingsSent = false;

    private bool $compressionEnabled = false;

    private ?string $clientPublicKey = null;

    /** @var ?array{private: string, public: string} */
    private ?array $serverKeys = null;

    private ?EncryptionContext $inEncryption = null;

    private ?EncryptionContext $outEncryption = null;

    private bool $handshakeDone = false;

    private ?string $pendingKey = null;

    private bool $hasWaitingHandshakeAck = false;

    private float $x = 0.0;

    private float $y = 0.0;

    private float $z = 0.0;

    private int $runtimeId;

    private int $raknetState = self::RN_CONNECTING;

    /** @var array<int, bool> */
    private array $receivedReliable = [];

    private int $mcpeState = self::MC_NONE;

    /** @var array<int, bool> */
    public array $completedSplits = [];

    private static int $nextRuntimeId = 1;

    private ?Socket $socket = null;

    private bool $hasWaitingRequestChunkRadiusAck = false;

    private bool $cacheEnabled = false;

    public function __construct(string $addr, int $port)
    {
        $this->address = $addr;
        $this->port = $port;
        $this->runtimeId = self::$nextRuntimeId++;
        $this->lastSeen = time();
        $this->orderedIndex = 0;
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
            throw new LogicException("Handshake JWT not set");
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
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
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
        return 0;
    }

    /**
     * @return array{yaw: float, pitch: float}
     */
    public function getRotation(): array
    {
        return ['yaw' => 0.0, 'pitch' => 0.0];
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

    public function getSocket(): ?Socket
    {
        return $this->socket;
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
            $pk = "\x15";
            @socket_sendto($this->socket, $pk, 1, 0, $this->address, $this->port);
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
        return $this->clientPublicKey ?? throw new RuntimeException("Client public key not set");
    }

    /**
     * @param array{private: string, public: string} $keys
     */
    public function setServerKeys(array $keys): void
    {
        $this->serverKeys = $keys;
    }

    /**
     * @return ?array{private: string, public: string}
     */
    public function getServerKeys(): ?array
    {
        return $this->serverKeys;
    }

    public function enableEncryption(?string $key): void
    {
        if ($key === null) {
            throw new RuntimeException("Key or IV is null");
        }
        if (\strlen($key) !== 32) {
            throw new RuntimeException("Invalid key length: " . \strlen($key));
        }
        $this->inEncryption = new EncryptionContext($key);
        $this->outEncryption = new EncryptionContext($key);
    }

    public function hasEncryption(): bool
    {
        return $this->inEncryption !== null || $this->outEncryption !== null;
    }

    public function isEncryptionEnabled(): bool
    {
        return $this->outEncryption !== null;
    }

    public function encrypt(string $data): string
    {
        return $this->outEncryption?->encrypt($data) ?? $data;
    }

    public function decrypt(string $data): string
    {
        return $this->inEncryption?->decrypt($data) ?? $data;
    }

    public function decodeInbound(string $data): string
    {
        // Attempt decryption only when inbound encryption context exists
        // and the payload does not start with a valid compression ID (0x00 or 0xFF).
        if ($this->inEncryption !== null && $data !== '') {
            $first = \ord($data[0]);
            try {
                Logger::debug("Inbound decrypt attempt: raw first=0x" . dechex($first) . " len=" . \strlen($data));
                Logger::debug("Inbound raw hex=" . bin2hex(substr($data, 0, min(32, \strlen($data)))));
                $data = $this->decrypt($data);
                Logger::debug("Inbound decrypt succeeded, payload head=0x" . dechex(\ord($data[0] ?? "\x00")) . " len=" . \strlen($data));
            } catch (RuntimeException $e) {
                // Decryption failed: abort processing so caller can handle failure.
                Logger::debug("Inbound decrypt attempt failed: " . $e->getMessage());
                throw $e;
            }
        }
        if ($this->shouldDecompressInbound()) {
            if ($data === '') {
                throw new RuntimeException("Empty packet, cannot read compression ID");
            }
            $compressionId = \ord($data[0]);
            $compressedPayload = substr($data, 1);
            if ($compressionId === 0x0) {
                $decoded = @gzinflate($compressedPayload);
                if ($decoded === false) {
                    throw new RuntimeException("Raw deflate decode failed");
                }
                $data = $decoded;
            } elseif ($compressionId === 0xff) {
                $data = $compressedPayload;
                // No compression
            } else {
                throw new RuntimeException("Unknown compression ID: 0x" . dechex($compressionId));
            }
        }
        return $data;
    }

    public function encodeOutbound(string $data): string
    {
        if ($this->shouldCompressOutbound()) {
            $compressed = gzdeflate($data, 7);
            if ($compressed === false) {
                throw new RuntimeException('gzdeflate failed');
            }
            $data = "\x00" . $compressed;
        }
        if ($this->isEncryptionEnabled()) {
            $data = $this->encrypt($data);
        }
        return "\xfe" . $data;
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

    public function setPendingEncryption(string $key): void
    {
        $this->pendingKey = $key;
    }

    public function hasPendingEncryption(): bool
    {
        return $this->pendingKey !== null;
    }

    public function enablePendingEncryption(): void
    {
        if ($this->pendingKey === null) {
            throw new LogicException("No pending encryption keys available");
        }
        // Prepare to decrypt incoming packets from client immediately,
        // but do not enable outbound encryption until handshake finalization.
        if ($this->inEncryption === null) {
            $this->inEncryption = new EncryptionContext($this->pendingKey);
        }
        Logger::debug("Pending decryption activated (inbound only)");
    }

    public function finalizeEncryption(): void
    {
        if ($this->pendingKey === null) {
            throw new LogicException('No pending encryption to finalize');
        }
        // Enable outbound encryption now that handshake exchange is complete.
        $this->outEncryption = new EncryptionContext($this->pendingKey);
        $this->pendingKey = null;
        $this->handshakeDone = true;
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

    public function nextOrderedIndex(): int
    {
        return $this->orderedIndex++;
    }

    public function storeFragment(int $fragmentId, int $fragmentCount, int $fragmentIndex, string $payload): bool
    {
        $this->fragments[$fragmentId]['count'] ??= $fragmentCount;
        $this->fragments[$fragmentId]['parts'][$fragmentIndex] = $payload;
        return \count($this->fragments[$fragmentId]['parts']) >= $fragmentCount;
    }

    public function consumeFragments(int $fragmentId): string
    {
        ksort($this->fragments[$fragmentId]['parts']);
        $data = implode('', $this->fragments[$fragmentId]['parts']);
        unset($this->fragments[$fragmentId]);
        return $data;
    }

    public function markNetworkSettingsReliableSeq(int $seq): void
    {
        $this->networkSettingsReliableSeq = $seq;
    }

    public function isAckForNetworkSettings(int $seq): bool
    {
        return $this->networkSettingsSent && $this->networkSettingsReliableSeq !== null && $seq === $this->networkSettingsReliableSeq;
    }

    public function clearNetworkSettingsReliableSeq(): void
    {
        $this->networkSettingsReliableSeq = null;
    }

    public function getPlayerName(): string
    {
        return $this->username;
    }

    public function markReliableReceived(int $reliableIndex): bool
    {
        if (isset($this->receivedReliable[$reliableIndex])) {
            return false;
        }
        $this->receivedReliable[$reliableIndex] = true;
        if (\count($this->receivedReliable) > 4096) {
            array_shift($this->receivedReliable);
        }
        return true;
    }

    public function hasWaitingRequestChunkRadiusAck(): bool
    {
        return $this->hasWaitingRequestChunkRadiusAck;
    }

    public function setWaitingRequestChunkRadiusAck(bool $v): void
    {
        $this->hasWaitingRequestChunkRadiusAck = $v;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled(bool $v): void
    {
        $this->cacheEnabled = $v;
    }
}
