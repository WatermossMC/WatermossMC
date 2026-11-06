<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

final class DiscoveryCodec
{
    public const PORT = 7551;

    public const PACKET_REQUEST = 0;

    public const PACKET_RESPONSE = 1;

    public const PACKET_MESSAGE = 2;

    public const SERVER_INFO_VERSION = 7;

    public const CONNECTION_TYPE_NETHERNET = 4;

    private static ?string $key = null;

    private static function key(): string
    {
        return self::$key ??= hash('sha256', pack('P', 0xdeadbeef), true);
    }

    public static function encode(int $packetId, string $senderId, string $body): string
    {
        $inner = pack('v', $packetId) . Uint64::toLe($senderId) . str_repeat("\0", 8) . $body;
        $payload = pack('v', \strlen($inner) + 2) . $inner;
        $enc = openssl_encrypt($payload, 'aes-256-ecb', self::key(), \OPENSSL_RAW_DATA);
        if ($enc === false) {
            throw new \RuntimeException('Discovery encrypt failed');
        }
        return hash_hmac('sha256', $payload, self::key(), true) . $enc;
    }

    /** @return array{id: int, sender: string, body: string}|null */
    public static function decode(string $data): ?array
    {
        if (\strlen($data) < 32 + 16 || (\strlen($data) - 32) % 16 !== 0) {
            return null;
        }
        $payload = openssl_decrypt(substr($data, 32), 'aes-256-ecb', self::key(), \OPENSSL_RAW_DATA);
        if ($payload === false || \strlen($payload) < 20) {
            return null;
        }
        if (!hash_equals(hash_hmac('sha256', $payload, self::key(), true), substr($data, 0, 32))) {
            return null;
        }
        $h = unpack('vtype', $payload, 2);
        if ($h === false) {
            return null;
        }
        return ['id' => $h['type'], 'sender' => Uint64::fromLe(substr($payload, 4, 8)), 'body' => substr($payload, 20)];
    }

    /** Message packet body: u64le recipient | u32le len | signal. */
    public static function encodeMessageBody(string $recipient, string $signal): string
    {
        return Uint64::toLe($recipient) . pack('V', \strlen($signal)) . $signal;
    }

    /** @return array{recipient: string, signal: string}|null */
    public static function decodeMessageBody(string $body): ?array
    {
        if (\strlen($body) < 12) {
            return null;
        }
        $len = unpack('Vlen', $body, 8);
        if ($len === false || 12 + $len['len'] !== \strlen($body)) {
            return null;
        }
        return ['recipient' => Uint64::fromLe(substr($body, 0, 8)), 'signal' => substr($body, 12)];
    }

    /** Response packet body: u32le len | hex(ServerInfo). */
    public static function encodeResponseBody(string $serverInfo): string
    {
        $hex = bin2hex($serverInfo);
        return pack('V', \strlen($hex)) . $hex;
    }

    public static function encodeServerInfo(
        string $serverName,
        int $protocol,
        string $gameVersion,
        string $levelName,
        int $gameType,
        int $players,
        int $maxPlayers,
        bool $onlineAuth,
        bool $selfSignedAuth,
        string $nonce = '',
        bool $editorWorld = false,
        bool $hardcore = false
    ): string {
        return \chr(self::SERVER_INFO_VERSION)
            . self::str($serverName)
            . self::zigzag($protocol)
            . self::str($gameVersion)
            . self::str($levelName)
            . self::zigzag($players)
            . self::zigzag($maxPlayers)
            . self::zigzag($gameType)
            . \chr($editorWorld ? 1 : 0)
            . \chr($hardcore ? 1 : 0)
            . \chr($onlineAuth ? 1 : 0)
            . \chr($selfSignedAuth ? 1 : 0)
            . self::str($nonce)
            . self::zigzag(self::CONNECTION_TYPE_NETHERNET);
    }

    private static function str(string $s): string
    {
        return self::varuint(\strlen($s)) . $s;
    }

    private static function zigzag(int $v): string
    {
        return self::varuint($v >= 0 ? $v << 1 : ((-$v) << 1) - 1);
    }

    private static function varuint(int $v): string
    {
        $out = '';
        do {
            $b = $v & 0x7F;
            $v >>= 7;
            $out .= \chr($v > 0 ? $b | 0x80 : $b);
        } while ($v > 0);
        return $out;
    }
}
