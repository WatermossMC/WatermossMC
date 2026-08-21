<?php

declare(strict_types=1);

namespace watermossmc\mcpe;

use Socket;
use Throwable;
use watermossmc\binary\Binary;
use watermossmc\mcpe\handler\DisconnectPacketHandler;
use watermossmc\mcpe\handler\HandshakePacketHandler;
use watermossmc\mcpe\handler\InGamePacketHandler;
use watermossmc\mcpe\handler\LoginPacketHandler;
use watermossmc\mcpe\handler\NetworkSettingsPacketHandler;
use watermossmc\mcpe\handler\ResourcePackPacketHandler;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\Server;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class PacketDispatcher
{
    private static ?World $world = null;

    private static ?Server $server = null;

    private static ?InboundPacketRegistry $inboundPacketRegistry = null;

    /** @var array<int, PacketHandler> */
    private static array $handlers = [];

    private static bool $initialized = false;

    public static function setServer(Server $server): void
    {
        self::$server = $server;
        self::initHandlers();
    }

    public static function setWorld(?World $world): void
    {
        self::$world = $world;
        self::initHandlers();
    }

    private static function initHandlers(): void
    {
        if (self::$initialized) {
            return;
        }
        $world = self::$world;
        $server = self::$server;

        $phaseHandlers = [
            new DisconnectPacketHandler(),
            new NetworkSettingsPacketHandler(),
            new LoginPacketHandler(),
            new HandshakePacketHandler(),
            new ResourcePackPacketHandler($world, $server),
            $inGame = new InGamePacketHandler($world, $server),
        ];

        foreach ($phaseHandlers as $handler) {
            foreach ($handler->packetIds() as $pid) {
                if (isset(self::$handlers[$pid])) {
                    throw new \RuntimeException("Duplicate handler registered for packet ID 0x" . dechex($pid));
                }
                self::$handlers[$pid] = $handler;
            }
        }
        self::$initialized = true;
    }

    public static function registerInboundPacket(int $packetId, callable $handler, bool $replace = false): void
    {
        self::getInboundPacketRegistry()->register($packetId, $handler, $replace);
    }

    public static function getInboundPacketRegistry(): InboundPacketRegistry
    {
        return self::$inboundPacketRegistry ??= new InboundPacketRegistry();
    }

    public static function syncPlayers(): void
    {
        // Placeholder for player sync if called from Server.php
    }

    public static function handleBatch(string $data, Session $session, Socket $socket): void
    {
        self::initHandlers();
        $rawDataLen = \strlen($data);
        try {
            $data = $session->decodeInbound($data);
        } catch (Throwable $e) {
            Logger::error("Decode inbound failed: {$e->getMessage()}");
            return;
        }
        $decodedLen = \strlen($data);
        Logger::debug("[Network] Batch RX: {$rawDataLen} raw -> {$decodedLen} decoded | Session: {$session->getRuntimeId()} | State: {$session->getMcpeState()}");
        $offset = 0;
        $count = 0;
        while ($offset < $decodedLen) {
            try {
                $len = Binary::readVarInt($data, $offset);
                if ($len === 0) {
                    continue;
                }
                if ($len < 0 || $offset + $len > $decodedLen) {
                    Logger::warning("[Network] Packet length mismatch @ offset {$offset}. Len: {$len}, Remaining: " . ($decodedLen - $offset));
                    break;
                }
                $packet = substr($data, $offset, $len);
                $offset += $len;
                $count++;
                self::handlePacket($packet, $session, $socket);
            } catch (Throwable $e) {
                Logger::error("[Network] Batch packet processing error @ offset {$offset}: {$e->getMessage()}");
                break;
            }
        }
        Logger::debug("[Network] Batch processed: {$count} packets.");
        RakNet::flush($session, $socket);
    }

    private static function handlePacket(string $packet, Session $session, Socket $socket): void
    {
        $pidHex = '??';
        try {
            $o = 0;
            $pid = Binary::readVarInt($packet, $o);
            $pidHex = strtoupper(dechex($pid));
            Logger::debug("[RX] Packet 0x{$pidHex} | State: {$session->getMcpeState()} | Size: " . \strlen($packet) . " bytes");
            if (self::getInboundPacketRegistry()->dispatch($pid, $packet, $o, $session, $socket)) {
                return;
            }

            if (!isset(self::$handlers[$pid])) {
                Logger::debug("[RX] Unhandled packet 0x{$pidHex}");
                return;
            }

            $handler = self::$handlers[$pid];
            if ($handler instanceof InGamePacketHandler) {
                $handler->handlePacket($pid, $packet, $o, $session, $socket);
            } else {
                $handler->handle($packet, $o, $session, $socket);
            }
        } catch (Throwable $e) {
            Logger::error("Packet handling error [PID: 0x{$pidHex}]: {$e->getMessage()}");
            Logger::debug($e->getTraceAsString());
            protocol\clientbound\Disconnect::send($session, $socket, "Internal Server Error");
            RakNet::flush($session, $socket);
        }
    }
}
