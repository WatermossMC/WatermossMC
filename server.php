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

use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\TickLoop;
use watermossmc\mcpe\PacketDispatcher;
use watermossmc\Server;
use watermossmc\util\Config;
use watermossmc\util\Logger;
use watermossmc\VersionInfo;

require __DIR__ . '/vendor/autoload.php';

Config::load(__DIR__ . '/server.properties');

Logger::init();

$config = [
    'bind_ip' => Config::getString('server_ip', '0.0.0.0'),
    'bind_port' => Config::getInt('server_port', 19132),
    'max_players' => Config::getInt('max_players', 20),
    'motd' => Config::getString('motd', 'WatermossMC Server'),
];

$shutdown = false;

set_exception_handler(function (\Throwable $e) use (&$shutdown): void {
    Logger::exception($e);
    $shutdown = true;
});

// Signal handlers for graceful shutdown (guarded if pcntl is available)
if (\function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function () use (&$shutdown): void {
        Logger::info("Received SIGTERM, shutting down gracefully...");
        $shutdown = true;
    });

    pcntl_signal(SIGINT, function () use (&$shutdown): void {
        Logger::info("Received SIGINT, shutting down gracefully...");
        $shutdown = true;
    });
}

Logger::info('========================================');
Logger::success(VersionInfo::NAME . ' ' . VersionInfo::VERSION . ' (' . VersionInfo::CHANNEL . ')');
Logger::info('Minecraft Bedrock ' . VersionInfo::MINECRAFT_VERSION . ' | Plugin API ' . VersionInfo::getApiVersion());
Logger::info('Repository: ' . VersionInfo::REPOSITORY);
Logger::info('Starting server on ' . $config['bind_ip'] . ':' . $config['bind_port'], ['maxPlayers' => $config['max_players']]);
Logger::info('========================================');

// Create UDP socket
$socket = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
if ($socket === false) {
    $error = socket_strerror(socket_last_error());
    Logger::error("Failed to create UDP socket: $error");
    exit(1);
}

socket_set_option($socket, \SOL_SOCKET, \SO_REUSEADDR, 1);
socket_set_nonblock($socket);

if (!socket_bind($socket, $config['bind_ip'], $config['bind_port'])) {
    $error = socket_strerror(socket_last_error());
    Logger::error("Failed to bind {$config['bind_ip']}:{$config['bind_port']}: $error");
    socket_close($socket);
    exit(1);
}

RakNet::init();
Logger::info("RakNet initialized");

// Initialize server API and tick loop
$tickLoop = new TickLoop();
$server = new Server(__DIR__, $tickLoop);
PacketDispatcher::setServer($server);

$tickLoop->add(static function () use ($server): void {
    $server->tick();

    if ($server->getCurrentTick() % 20 === 0) {
        RakNet::tick();

        Logger::debug('Server heartbeat - Tick: ' . $server->getCurrentTick());
    }
});

$server->boot();

Logger::info("Server started successfully. Press Ctrl+C to stop.");

$buffer = '';
$fromIp = '';
$fromPort = 0;

$nextTick = hrtime(true);
$tickInterval = 1_000_000_000 / 20; // 20 TPS

Logger::info("Entering main server loop...");

while (!$shutdown) {
    // Handle signals (if available)
    if (\function_exists('pcntl_signal_dispatch')) {
        pcntl_signal_dispatch();
    }

    $read = [STDIN];
    $write = $except = null;
    if (stream_select($read, $write, $except, 0, 10000) > 0) {
        if (in_array(STDIN, $read, true)) {
            $line = fgets(STDIN);
            if ($line !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                // Remove leading slash if present
                if ($line[0] === '/') {
                    $line = substr($line, 1);
                }

                $server->getCommandMap()->execute(null, $line);
            }
        }
    }

    // Process incoming packets
    while (!$shutdown && @socket_recvfrom(
        $socket,
        $buffer,
        65535,
        \MSG_DONTWAIT,
        $fromIp,
        $fromPort
    )) {
        try {
            RakNet::handle($buffer, $fromIp, $fromPort, $socket);
        } catch (\Throwable $e) {
            Logger::error("Error handling packet from {$fromIp}:{$fromPort}: " . $e->getMessage());
        }
        $buffer = '';
    }

    $now = hrtime(true);
    if ($now >= $nextTick) {
        try {
            $tickLoop->runOnce();
            $nextTick += $tickInterval;
        } catch (\Throwable $e) {
            Logger::error("Error in tick loop: " . $e->getMessage());
        }
    }

    usleep(1000);
}

// Cleanup
Logger::info("Shutting down server...");
$server->shutdown();
socket_close($socket);
Logger::info("Server shutdown complete.");
