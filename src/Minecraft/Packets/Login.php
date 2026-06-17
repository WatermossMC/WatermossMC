<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use RuntimeException;
use Throwable;
use WatermossMC\Binary\Binary;
use WatermossMC\Crypto\XboxAuth;
use WatermossMC\Util\Logger;

final class Login extends Packet
{
    /**
     * @return array{
     *   protocol: int,
     *   chain: array<int, string>,
     *   clientJwt: string,
     *   payload: array<string, mixed>,
     *   identityPublicKey: string|null,
     *   ecdhPublicKey: string|null
     * }
     */
    public static function read(string $p, int &$o): array
    {
        $protocol = Binary::readInt($p, $o);

        $connLen = Binary::readVarInt($p, $o);
        if ($o + $connLen > strlen($p)) {
            throw new RuntimeException("Buffer underflow");
        }

        $conn = substr($p, $o, $connLen);
        $o += $connLen;

        $io = 0;
        $authLen = Binary::readLInt($conn, $io);
        $authRaw = substr($conn, $io, $authLen);
        $io += $authLen;

        try {
            $authInfo = json_decode($authRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException("Login JSON corrupt: " . $e->getMessage());
        }

        if (!is_array($authInfo)) {
            throw new RuntimeException("Login JSON did not decode to an object");
        }

        $chain = $authInfo['chain'] ?? null;

        if ($chain === null && isset($authInfo['Certificate'])) {
            $certData = is_string($authInfo['Certificate'])
                ? json_decode($authInfo['Certificate'], true)
                : $authInfo['Certificate'];
            if (!is_array($certData)) {
                $certData = [];
            }
            $chain = $certData['chain'] ?? null;
        }

        if (!is_array($chain) || empty($chain)) {
            Logger::debug("Auth Data: " . json_encode($authInfo));
            throw new RuntimeException("Login chain missing or malformed");
        }

        /** @var array<int, string> $chain */
        $chain = array_values(array_filter($chain, 'is_string'));

        /** @var array<string, mixed> $payload */
        $payload = [];
        $identityPublicKey = null;

        try {
            $authResult = XboxAuth::validate($chain);
            $identityPublicKey = $authResult['identityPublicKey'];
            $payload = $authResult['data'];
        } catch (Throwable $e) {
            foreach ($chain as $jwt) {
                $parts = explode('.', $jwt);
                if (count($parts) < 2) {
                    continue;
                }

                $body = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (!is_array($body)) {
                    continue;
                }

                if (isset($body['identityPublicKey']) && is_string($body['identityPublicKey'])) {
                    $identityPublicKey = $body['identityPublicKey'];
                }
                if (isset($body['extraData']) && is_array($body['extraData'])) {
                    /** @var array<string, mixed> $extraData */
                    $extraData = $body['extraData'];
                    $payload = array_merge($payload, $extraData);
                }
            }
        }

        // Ambil ECDH public key dari header x5u JWT terakhir di chain
        $ecdhPublicKey = null;
        $lastJwt = end($chain);
        if (is_string($lastJwt)) {
            $parts = explode('.', $lastJwt);
            if (isset($parts[0])) {
                $header = json_decode(
                    base64_decode(strtr($parts[0], '-_', '+/')),
                    true
                );
                if (is_array($header) && isset($header['x5u']) && is_string($header['x5u'])) {
                    $ecdhPublicKey = $header['x5u'];
                    Logger::debug("[Login] ECDH public key (x5u from last chain JWT): " . substr($ecdhPublicKey, 0, 32) . "...");
                }
            }
        }

        if ($ecdhPublicKey === null) {
            Logger::warning("[Login] ecdhPublicKey not found in last chain JWT header, falling back to identityPublicKey");
            $ecdhPublicKey = $identityPublicKey;
        }

        $clientJwtLen = Binary::readLInt($conn, $io);
        $clientJwt = substr($conn, $io, $clientJwtLen);

        if ($clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $clientData = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($clientData)) {
                    /** @var array<string, mixed> $clientData */
                    $payload = array_merge($payload, $clientData);
                }
            }
        }

        $displayName = 'unknown';
        if (isset($payload['displayName']) && is_string($payload['displayName'])) {
            $displayName = $payload['displayName'];
        }

        Logger::info("Login Success: {$displayName} (Protocol: {$protocol})");

        return [
            'protocol'          => $protocol,
            'chain'             => $chain,
            'clientJwt'         => $clientJwt,
            'payload'           => $payload,
            'identityPublicKey' => $identityPublicKey,
            'ecdhPublicKey'     => $ecdhPublicKey,
        ];
    }
}