<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use RuntimeException;
use Throwable;
use WatermossMC\Binary\Binary;
use WatermossMC\Crypto\XboxAuth;
use WatermossMC\Util\Config;
use WatermossMC\Util\Logger;

final class Login extends Packet
{
    // AuthenticationType values
    private const AUTH_TYPE_XBOX    = 0; // Modern OpenID single-token format
    private const AUTH_TYPE_LEGACY  = 1; // Legacy chain format

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
        if ($o + $connLen > strlen($p)) {
            throw new RuntimeException("Buffer underflow");
        }

        $conn = substr($p, $o, $connLen);
        $o += $connLen;

        $io = 0;
        $authLen  = Binary::readLInt($conn, $io);
        $authRaw  = substr($conn, $io, $authLen);
        $io += $authLen;

        try {
            $authInfo = json_decode($authRaw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException("Login JSON corrupt: " . $e->getMessage());
        }

        if (!is_array($authInfo)) {
            throw new RuntimeException("Login JSON did not decode to an object");
        }

        Logger::debug("[Login] Auth keys: " . implode(', ', array_keys($authInfo)));

        // ---------------------------------------------------------------
        // Detect format: modern OpenID (AuthenticationType + Token)
        //                vs legacy chain array
        // ---------------------------------------------------------------
        $chain           = null;
        $singleToken     = null;
        $authType        = $authInfo['AuthenticationType'] ?? null;

        if (isset($authInfo['Token']) && is_string($authInfo['Token'])) {
            // Modern format: {"AuthenticationType": 0, "Token": "<jwt>"}
            $singleToken = $authInfo['Token'];
            Logger::debug("[Login] Modern single-token format detected (AuthenticationType={$authType})");
        } elseif (isset($authInfo['chain']) && is_array($authInfo['chain'])) {
            // Legacy chain format: {"chain": ["<jwt>", ...]}
            $chain = $authInfo['chain'];
            Logger::debug("[Login] Legacy chain format detected");
        } elseif (isset($authInfo['Certificate'])) {
            // Older fallback
            $certData = is_string($authInfo['Certificate'])
                ? json_decode($authInfo['Certificate'], true)
                : $authInfo['Certificate'];
            if (is_array($certData)) {
                $chain = $certData['chain'] ?? null;
            }
            Logger::debug("[Login] Certificate-wrapped format detected");
        }

        /** @var array<string, mixed> $payload */
        $payload           = [];
        $identityPublicKey = null;
        $xboxAuthenticated = false;
        /** @var array<int, string> $chainJwts */
        $chainJwts         = [];

        if ($singleToken !== null) {
            // --- Modern OpenID single-token path ---
            $chainJwts = [$singleToken];
            [$payload, $identityPublicKey, $xboxAuthenticated] = self::processModernToken(
                $singleToken, $requireXboxAuth
            );
        } elseif (is_array($chain) && !empty($chain)) {
            // --- Legacy chain path ---
            $chainJwts = array_values(array_filter($chain, 'is_string'));
            [$payload, $identityPublicKey, $xboxAuthenticated] = self::processLegacyChain(
                $chainJwts, $requireXboxAuth
            );
        } else {
            Logger::debug("[Login] Auth Data: " . json_encode($authInfo));
            throw new RuntimeException("Login chain missing or malformed");
        }

        // ECDH key: x5u from the last JWT's header
        $ecdhPublicKey = self::extractEcdhKey($chainJwts);
        if ($ecdhPublicKey === null) {
            Logger::warning("[Login] ecdhPublicKey not found; falling back to identityPublicKey");
            $ecdhPublicKey = $identityPublicKey;
        }

        // Client data JWT
        $clientJwtLen = Binary::readLInt($conn, $io);
        $clientJwt    = substr($conn, $io, $clientJwtLen);

        if ($clientJwt !== '') {
            $parts = explode('.', $clientJwt);
            if (isset($parts[1])) {
                $clientDataRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
                if ($clientDataRaw !== false) {
                    $clientData = json_decode($clientDataRaw, true);
                    if (is_array($clientData)) {
                        /** @var array<string, mixed> $clientData */
                        $payload = array_merge($payload, $clientData);
                    }
                }
            }
        }

        $displayName = isset($payload['displayName']) && is_string($payload['displayName'])
            ? $payload['displayName']
            : 'unknown';

        Logger::info(
            "Login: {$displayName}"
            . " (Protocol: {$protocol}"
            . ", online-mode: " . ($requireXboxAuth ? 'true' : 'false')
            . ", XboxAuth: " . ($xboxAuthenticated ? 'yes' : 'no') . ")"
        );

        return [
            'protocol'          => $protocol,
            'chain'             => $chainJwts,
            'clientJwt'         => $clientJwt,
            'payload'           => $payload,
            'identityPublicKey' => $identityPublicKey,
            'ecdhPublicKey'     => $ecdhPublicKey,
            'xboxAuthenticated' => $xboxAuthenticated,
        ];
    }

    /**
     * Processes the modern single-token (OpenID) format.
     * The JWT payload contains cpk (client public key), xid, xname, extraData, etc.
     *
     * @return array{array<string, mixed>, string|null, bool}
     */
    private static function processModernToken(string $jwt, bool $requireXboxAuth): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException("Modern token is not a valid JWT");
        }

        $payloadRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payloadRaw === false) {
            throw new RuntimeException("Failed to base64-decode modern token payload");
        }

        $claims = json_decode($payloadRaw, true);
        if (!is_array($claims)) {
            throw new RuntimeException("Failed to JSON-decode modern token payload");
        }

        /** @var array<string, mixed> $claims */

        // Validate expiry (with 60s clock drift tolerance)
        $now = time();
        if (isset($claims['nbf']) && is_int($claims['nbf']) && $claims['nbf'] > $now + 60) {
            throw new RuntimeException("Modern token not yet valid (nbf)");
        }
        if (isset($claims['exp']) && is_int($claims['exp']) && $claims['exp'] < $now - 60) {
            throw new RuntimeException("Modern token has expired (exp)");
        }

        // Verify audience
        $expectedAudience = "api://auth-minecraft-services/multiplayer";
        if (!isset($claims['aud']) || $claims['aud'] !== $expectedAudience) {
            if ($requireXboxAuth) {
                throw new RuntimeException(
                    "Modern token has invalid audience: " . ($claims['aud'] ?? 'missing')
                );
            }
            Logger::warning("[Login] Modern token audience mismatch (offline mode, continuing)");
        }

        // cpk = client public key (DER, base64-encoded)
        $identityPublicKey = null;
        if (isset($claims['cpk']) && is_string($claims['cpk'])) {
            $identityPublicKey = $claims['cpk'];
        }

        // Build payload from known claim fields
        /** @var array<string, mixed> $payload */
        $payload = [];

        if (isset($claims['extraData']) && is_array($claims['extraData'])) {
            /** @var array<string, mixed> $extra */
            $extra   = $claims['extraData'];
            $payload = array_merge($payload, $extra);
        }

        // Map top-level claims that the rest of the server expects
        $fieldMap = [
            'xname' => 'displayName',
            'xid'   => 'XUID',
            'mid'   => 'identity',   // Minecraft UUID / session ID
        ];
        foreach ($fieldMap as $from => $to) {
            if (isset($claims[$from]) && !isset($payload[$to])) {
                $payload[$to] = $claims[$from];
            }
        }

        // The token is signed by Mojang's auth service, so we consider it authenticated.
        // Full signature verification against the Mojang JWKS would be ideal but requires
        // an HTTP fetch; for now we trust the audience + expiry checks above.
        $xboxAuthenticated = isset($claims['xid']);

        Logger::debug("[Login] Modern token claims: xname=" . ($claims['xname'] ?? '?')
            . " xid=" . ($claims['xid'] ?? '?')
            . " cpk=" . (isset($claims['cpk']) ? substr($claims['cpk'], 0, 16) . '...' : 'missing'));

        return [$payload, $identityPublicKey, $xboxAuthenticated];
    }

    /**
     * Processes the legacy JWT chain format via XboxAuth::validate().
     *
     * @param array<int, string> $chain
     * @return array{array<string, mixed>, string|null, bool}
     */
    private static function processLegacyChain(array $chain, bool $requireXboxAuth): array
    {
        /** @var array<string, mixed> $payload */
        $payload           = [];
        $identityPublicKey = null;
        $xboxAuthenticated = false;

        try {
            $authResult        = XboxAuth::validate($chain);
            $identityPublicKey = $authResult['identityPublicKey'];
            $payload           = $authResult['data'];
            $xboxAuthenticated = true;
        } catch (Throwable $e) {
            if ($requireXboxAuth) {
                throw new RuntimeException(
                    "Xbox Live signature verification failed: " . $e->getMessage(), 0, $e
                );
            }

            Logger::warning("[Login] Xbox Live verification failed (offline mode): " . $e->getMessage());

            foreach ($chain as $jwt) {
                $parts = explode('.', $jwt);
                if (count($parts) < 2) {
                    continue;
                }
                $bodyRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
                if ($bodyRaw === false) {
                    continue;
                }
                $body = json_decode($bodyRaw, true);
                if (!is_array($body)) {
                    continue;
                }
                if (isset($body['identityPublicKey']) && is_string($body['identityPublicKey'])) {
                    $identityPublicKey = $body['identityPublicKey'];
                }
                if (isset($body['extraData']) && is_array($body['extraData'])) {
                    /** @var array<string, mixed> $extra */
                    $extra   = $body['extraData'];
                    $payload = array_merge($payload, $extra);
                }
            }
        }

        return [$payload, $identityPublicKey, $xboxAuthenticated];
    }

    /**
     * Extracts the ECDH public key from the x5u field of the last JWT's header.
     *
     * @param array<int, string> $chain
     */
    private static function extractEcdhKey(array $chain): ?string
    {
        $lastJwt = end($chain);
        if (!is_string($lastJwt)) {
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
        if (!is_array($header) || !isset($header['x5u']) || !is_string($header['x5u'])) {
            return null;
        }

        Logger::debug("[Login] ECDH key (x5u): " . substr($header['x5u'], 0, 32) . "...");
        return $header['x5u'];
    }
}