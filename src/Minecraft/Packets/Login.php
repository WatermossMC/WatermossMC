<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;
use WatermossMC\Util\Logger;

final class Login extends Packet
{
    /**
     * @return array{
     *     protocol:int,
     *     chain:list<string>,
     *     clientJwt:string,
     *     payload:?array<string,mixed>,
     *     identityPublicKey:string
     * }
     */
    public static function read(string $p, int &$o): array
    {
        $protocol = Binary::readInt($p, $o);

        $connLen = Binary::readVarInt($p, $o);
        $conn = substr($p, $o, $connLen);
        $o += $connLen;

        $io = 0;

        $authLen = Binary::readLInt($conn, $io);
        $authRaw = substr($conn, $io, $authLen);
        $io += $authLen;

        Logger::debug("AuthInfo RAW: " . substr($authRaw, 0, 80));

        $authInfo = json_decode($authRaw, true);
        if (!\is_array($authInfo) || !isset($authInfo['Certificate'])) {
            throw new \RuntimeException("Malformed authInfo");
        }

        $cert = json_decode($authInfo['Certificate'], true);
        if (!\is_array($cert) || !isset($cert['chain'])) {
            throw new \RuntimeException("Invalid Certificate JSON");
        }

        $chain = $cert['chain'];

        $clientJwtLen = Binary::readLInt($conn, $io);
        $clientJwt = substr($conn, $io, $clientJwtLen);

        $payload = [];
        $identityPublicKey = null;

        foreach ($chain as $jwt) {
            if (!\is_string($jwt)) {
                continue;
            }

            $parts = explode('.', $jwt);
            if (\count($parts) !== 3) {
                continue;
            }

            $body = json_decode(self::b64($parts[1]), true);
            if (!\is_array($body)) {
                continue;
            }

            if (isset($body['identityPublicKey'])) {
                $identityPublicKey = $body['identityPublicKey'];
            }

            if (isset($body['extraData'])) {
                $payload = $body['extraData'];
            }
        }

        if ($clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $decoded = json_decode(self::b64($parts[1]), true);
                if (\is_array($decoded)) {
                    $payload = array_merge($payload, $decoded);
                }
            }
        }

        Logger::debug("Login OK name=" . ($payload['displayName'] ?? 'unknown'));
        Logger::debug("identityPublicKey=" . ($identityPublicKey ? 'YES' : 'NO'));

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
            throw new \RuntimeException("Invalid base64");
        }

        return $decoded;
    }
}
