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

declare(strict_types=1);

namespace watermossmc\mcpe\protocol\handshake;

use RuntimeException;
use Throwable;
use watermossmc\binary\Binary;
use watermossmc\crypto\XboxAuth;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\util\Config;
use watermossmc\util\Logger;

final class Login extends Packet
{
    // AuthenticationType values
    private const AUTH_TYPE_XBOX = 0;

    // Modern OpenID single-token format
    private const AUTH_TYPE_LEGACY = 1;

    // Legacy chain format
    /**
     * @param bool|null $requireXboxAuth
     * @return array{
     *   protocol: int,
     *   chain: array<int, string>,
     *   clientJwt: string,
     *   payload: array<string, mixed>,
     *   identityPublicKey: string|null,
     *   ecdhPublicKey: string|null,
     *   xboxAuthenticated: bool
     * }
     */
    public static function read(string $p, int &$o, ?bool $requireXboxAuth = null): array
    {
        $requireXboxAuth ??= Config::getBool('online-mode', true);
        $protocol = Binary::readInt($p, $o);
        $connLen = Binary::readVarInt($p, $o);
        if ($o + $connLen > \strlen($p)) {
            throw new RuntimeException("Buffer underflow");
        }
        $conn = substr($p, $o, $connLen);
        $o += $connLen;
        $io = 0;
        $authLen = Binary::readLInt($conn, $io);
        if ($io + $authLen > \strlen($conn)) {
            throw new RuntimeException("Auth info buffer underflow");
        }
        $authRaw = substr($conn, $io, $authLen);
        $io += $authLen;
        try {
            $authInfo = json_decode((string) $authRaw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException("Login JSON corrupt: " . $e->getMessage());
        }
        if (!\is_array($authInfo)) {
            throw new RuntimeException("Login JSON did not decode to an array");
        }
        $singleToken = $authInfo['Token'] ?? null;
        $chain = $authInfo['chain'] ?? null;
        /** @var array<string, mixed> $payload */
        $payload = [];
        $identityPublicKey = null;
        $xboxAuthenticated = false;
        $chainJwts = [];
        if (\is_string($singleToken)) {
            $chainJwts = [$singleToken];
            [$payload, $identityPublicKey, $xboxAuthenticated] = self::processModernToken($singleToken, $requireXboxAuth);
        } elseif (\is_array($chain) && !empty($chain)) {
            $chainJwts = array_values(array_filter($chain, 'is_string'));
            [$payload, $identityPublicKey, $xboxAuthenticated] = self::processLegacyChain($chainJwts, $requireXboxAuth);
        } else {
            throw new RuntimeException("Login authentication data missing or malformed");
        }
        $ecdhPublicKey = self::extractEcdhKey($chainJwts) ?? $identityPublicKey;
        $clientJwtLen = Binary::readLInt($conn, $io);
        if ($io + $clientJwtLen > \strlen($conn)) {
            throw new RuntimeException("Client JWT buffer underflow");
        }
        $clientJwt = substr($conn, $io, $clientJwtLen);
        if ($clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $clientDataRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
                if ($clientDataRaw !== false) {
                    $clientData = json_decode($clientDataRaw, true);
                    if (\is_array($clientData)) {
                        $payload = array_merge($payload, $clientData);
                    }
                }
            }
        }
        $displayName = $payload['displayName'] ?? 'unknown';
        $xuid = $payload['XUID'] ?? '0';
        $identity = $payload['identity'] ?? '0';
        Logger::info("Login: {$displayName} (XUID: {$xuid}, UUID: {$identity}, Protocol: {$protocol})");
        return ['protocol' => $protocol, 'chain' => $chainJwts, 'clientJwt' => $clientJwt, 'payload' => $payload, 'displayName' => $displayName, 'XUID' => $xuid, 'identity' => $identity, 'identityPublicKey' => $identityPublicKey, 'ecdhPublicKey' => $ecdhPublicKey, 'xboxAuthenticated' => $xboxAuthenticated];
    }

    private static function processModernToken(string $jwt, bool $requireXboxAuth): array
    {
        $parts = explode('.', $jwt);
        if (\count($parts) !== 3) {
            throw new RuntimeException("Modern token is not a valid JWT");
        }
        $payloadRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payloadRaw === false) {
            throw new RuntimeException("Failed to base64-decode modern token payload");
        }
        $claims = json_decode($payloadRaw, true);
        if (!\is_array($claims)) {
            throw new RuntimeException("Failed to JSON-decode modern token payload");
        }
        $now = time();
        if (isset($claims['nbf']) && \is_int($claims['nbf']) && $claims['nbf'] > $now + 60) {
            throw new RuntimeException("Modern token not yet valid");
        }
        if (isset($claims['exp']) && \is_int($claims['exp']) && $claims['exp'] < $now - 60) {
            throw new RuntimeException("Modern token has expired");
        }
        $expectedAudience = "api://auth-minecraft-services/multiplayer";
        if (($claims['aud'] ?? null) !== $expectedAudience && $requireXboxAuth) {
            throw new RuntimeException("Modern token has invalid audience");
        }
        $identityPublicKey = $claims['cpk'] ?? null;
        $payload = $claims['extraData'] ?? [];
        if (!\is_array($payload)) {
            $payload = [];
        }
        // Map claims to normalized payload
        $payload['displayName'] = $claims['xname'] ?? 'unknown';
        $payload['XUID'] = $claims['xid'] ?? '0';
        $payload['identity'] = $claims['mid'] ?? '0';
        return [$payload, $identityPublicKey, isset($claims['xid'])];
    }

    private static function processLegacyChain(array $chain, bool $requireXboxAuth): array
    {
        $payload = [];
        $identityPublicKey = null;
        $xboxAuthenticated = false;
        try {
            $authResult = XboxAuth::validate($chain);
            $identityPublicKey = $authResult['identityPublicKey'];
            $payload = $authResult['data'];
            $xboxAuthenticated = true;
        } catch (Throwable $e) {
            if ($requireXboxAuth) {
                throw new RuntimeException("Xbox Live verification failed: " . $e->getMessage());
            }
            foreach ($chain as $jwt) {
                $parts = explode('.', $jwt);
                if (\count($parts) < 2) {
                    continue;
                }
                $bodyRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
                if ($bodyRaw === false) {
                    continue;
                }
                $body = json_decode($bodyRaw, true);
                if (!\is_array($body)) {
                    continue;
                }
                if (isset($body['identityPublicKey'])) {
                    $identityPublicKey = $body['identityPublicKey'];
                }
                if (isset($body['extraData']) && \is_array($body['extraData'])) {
                    $payload = array_merge($payload, $body['extraData']);
                }
            }
        }
        // Ensure normalized fields exist for legacy
        $payload['displayName'] ??= 'unknown';
        $payload['XUID'] ??= '0';
        $payload['identity'] ??= '0';
        return [$payload, $identityPublicKey, $xboxAuthenticated];
    }

    private static function extractEcdhKey(array $chain): ?string
    {
        $lastJwt = end($chain);
        if (!\is_string($lastJwt)) {
            return null;
        }
        $parts = explode('.', $lastJwt);
        if (!isset($parts[0])) {
            return null;
        }
        $headerRaw = base64_decode(strtr($parts[0], '-_', '+/'), true);
        if ($headerRaw === false) {
            return null;
        }
        $header = json_decode($headerRaw, true);
        return $header['x5u'] ?? null;
    }
}
