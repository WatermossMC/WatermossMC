<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;

final class Login extends Packet
{
    /**
     * @return array{
     *   protocol:int,
     *   chain:array<int,string>,
     *   clientJwt:string,
     *   payload:array<string,mixed>,
     *   identityPublicKey:?string
     * }
     */
    public static function read(string $p, int &$o): array
    {
        $offset = 0;

        $pid = Binary::readVarInt($p, $o);

        $protocol = Binary::readInt($p, $o);

        $json = Binary::readStringInt($p, $o);

        $data = json_decode($json, true);
        if (!\is_array($data)) {
            throw new \RuntimeException('Invalid Login JSON');
        }

        $chain = $data['chain'] ?? [];
        $clientJwt = $data['clientDataJwt'] ?? '';

        if (!\is_array($chain) || !\is_string($clientJwt)) {
            throw new \RuntimeException('Invalid Login structure');
        }

        $payload = [];
        $identityPublicKey = null;

        foreach ($chain as $token) {
            if (!\is_string($token)) {
                continue;
            }

            $parts = explode('.', $token);
            if (!isset($parts[1])) {
                continue;
            }

            $decoded = json_decode(self::b64($parts[1]), true);
            if (!\is_array($decoded)) {
                continue;
            }

            $payload = $decoded;

            if (isset($decoded['identityPublicKey'])) {
                $identityPublicKey = $decoded['identityPublicKey'];
            }
        }

        if ($payload === [] && $clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $decoded = json_decode(self::b64($parts[1]), true);
                if (\is_array($decoded)) {
                    $payload = $decoded;
                }
            }
        }

        return [
            'protocol' => $protocol,
            'chain' => $chain,
            'clientJwt' => $clientJwt,
            'payload' => $payload,
            'identityPublicKey' => $identityPublicKey,
        ];
    }

    private static function b64(string $data): string
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $pad = (4 - (\strlen($data) % 4)) % 4;

        $decoded = base64_decode($data . str_repeat('=', $pad), true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 data');
        }

        return $decoded;
    }
}
