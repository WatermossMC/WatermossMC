<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;

final class Login extends Packet
{
    /**
     * @return array{
     *   protocol:int,
     *   chain:string,
     *   jwt:string,
     *   payload:array<string,mixed>
     * }
     */
    public static function read(string $p, int &$o): array
    {
        $protocol = Binary::readInt($p, $o);

        $chainRaw = Binary::readStringInt($p, $o);
        $jwt = Binary::readStringInt($p, $o);

        $payload = [];

        $chainData = json_decode($chainRaw, true);
        if (\is_array($chainData) && isset($chainData['chain']) && \is_array($chainData['chain'])) {
            foreach ($chainData['chain'] as $token) {
                if (!\is_string($token)) {
                    continue;
                }
                $parts = explode('.', $token);
                if (isset($parts[1])) {
                    $decoded = json_decode(self::b64($parts[1]), true);
                    if (\is_array($decoded)) {
                        $payload = $decoded;
                    }
                }
            }
        }

        if ($payload === []) {
            $parts = explode('.', $jwt);
            if (isset($parts[1])) {
                $decoded = json_decode(self::b64($parts[1]), true);
                if (\is_array($decoded)) {
                    $payload = $decoded;
                }
            }
        }

        return [
            'protocol' => $protocol,
            'chain' => $chainRaw,
            'jwt' => $jwt,
            'payload' => $payload,
        ];
    }

    private static function b64(string $data): string
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $pad = (4 - \strlen($data) % 4) % 4;

        $decoded = base64_decode($data . str_repeat('=', $pad), true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64');
        }
        return $decoded;
    }
}
