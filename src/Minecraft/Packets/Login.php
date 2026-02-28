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

        $chain = $authInfo['chain'] ?? null;

        if ($chain === null && isset($authInfo['Certificate'])) {
            $certData = is_string($authInfo['Certificate']) 
                ? json_decode($authInfo['Certificate'], true) 
                : $authInfo['Certificate'];
            $chain = $certData['chain'] ?? null;
        }

        if (!is_array($chain) || empty($chain)) {
            Logger::debug("Auth Data: " . json_encode($authInfo));
            throw new RuntimeException("Login chain missing or malformed");
        }

        $payload = [];
        $identityPublicKey = null;

        try {
            $authResult = XboxAuth::validate($chain);
            $identityPublicKey = $authResult['identityPublicKey'];
            $payload = $authResult['data'];
        } catch (Throwable $e) {
            foreach ($chain as $jwt) {
                $parts = explode('.', $jwt);
                if (count($parts) < 2) continue;
                $body = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                
                if (isset($body['identityPublicKey'])) $identityPublicKey = $body['identityPublicKey'];
                if (isset($body['extraData'])) $payload = array_merge($payload, $body['extraData']);
            }
        }

        $clientJwtLen = Binary::readLInt($conn, $io);
        $clientJwt = substr($conn, $io, $clientJwtLen);

        if ($clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $clientData = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($clientData)) {
                    $payload = array_merge($payload, $clientData);
                }
            }
        }

        Logger::info("Login Success: " . ($payload['displayName'] ?? 'unknown') . " (Protocol: $protocol)");

        return [
            'protocol' => $protocol,
            'chain' => $chain,
            'clientJwt' => $clientJwt,
            'payload' => $payload,
            'identityPublicKey' => $identityPublicKey,
        ];
    }
}
