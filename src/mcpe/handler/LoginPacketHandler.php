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

namespace watermossmc\mcpe\handler;

use RuntimeException;
use Socket;
use Throwable;
use watermossmc\crypto\Crypto;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\PacketHandler;
use watermossmc\mcpe\protocol\clientbound\Disconnect;
use watermossmc\mcpe\protocol\handshake\Login;
use watermossmc\mcpe\protocol\handshake\ServerToClientHandshake;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\util\Logger;

final class LoginPacketHandler implements PacketHandler
{
    public function packetIds(): array
    {
        return [ProtocolInfo::LOGIN_PACKET];
    }

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool
    {
        Logger::debug("[0x01] Login packet received.");
        if ($session->getMcpeState() !== Session::MC_NETWORK) {
            Logger::warning("[0x01] Ignored: Session state is not MC_NETWORK.");
            return true;
        }
        try {
            $loginData = Login::read($packet, $offset);
        } catch (Throwable $e) {
            Logger::error("[0x01] Login::read() failed: {$e->getMessage()}");
            Disconnect::send($session, $socket, "Login parsing failed");
            return true;
        }
        $clientIdentityKey = $loginData['ecdhPublicKey'] ?? $loginData['identityPublicKey'];
        if (empty($clientIdentityKey)) {
            Logger::error("[0x01] Login failed: Missing identity key.");
            Disconnect::send($session, $socket, "Invalid login payload");
            return true;
        }
        $name = $loginData['displayName'] ?? 'unknown';
        $uuid = $loginData['payload']['identity'] ?? '0';
        $xuid = $loginData['payload']['XUID'] ?? '0';
        Logger::info("Login attempt: {$name} (UUID: {$uuid}, XUID: {$xuid})");
        try {
            $clientPem = Crypto::bedrockIdentityKeyToPem($clientIdentityKey);
            $session->setClientPublicKey($clientPem);
            $session->setLoginData((string) $uuid, (string) $name, (string) $xuid);
            $keys = Crypto::generateKeyPair();
            $session->setServerKeys($keys);
            $serverSalt = random_bytes(16);
            $sharedSecret = Crypto::deriveSecret($keys['private'], $session->getClientPublicKey());
            $serverPublicB64 = Crypto::pemToBase64($keys['public']);
            $jwt = $this->buildServerHandshakeJwt($serverPublicB64, $keys['private'], $serverSalt);

            ServerToClientHandshake::send($session, $socket, $jwt);
            RakNet::flush($session, $socket);
            usleep(50000);
            $key = Crypto::deriveAes($sharedSecret, $serverSalt);
            $session->setPendingEncryption($key);
            $session->enablePendingEncryption();
            $session->setWaitingHandshakeAck(true);
            $session->setMcpeState(Session::MC_LOGIN);
            Logger::debug("[0x03] State -> MC_LOGIN");
        } catch (Throwable $e) {
            Logger::error("[0x01] Crypto/handshake processing failed: {$e->getMessage()}");
            Disconnect::send($session, $socket, "Server handshake failed");
            RakNet::flush($session, $socket);
        }
        return true;
    }

    private function buildServerHandshakeJwt(string $serverPubKeyB64, string $serverPrivKeyPem, string $salt): string
    {
        $header = ['alg' => 'ES384', 'x5u' => $serverPubKeyB64];
        $payload = ['salt' => base64_encode($salt)];
        $b64Url = fn (string $data): string => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        $jwtHdr = $b64Url(json_encode($header, JSON_UNESCAPED_SLASHES));
        $jwtPld = $b64Url(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signingInput = $jwtHdr . '.' . $jwtPld;
        if (!openssl_sign($signingInput, $signature, $serverPrivKeyPem, \OPENSSL_ALGO_SHA384)) {
            throw new RuntimeException('Failed to sign handshake JWT');
        }
        $sigRaw = Crypto::derToSignature($signature, 48);
        return $signingInput . '.' . $b64Url($sigRaw);
    }
}
