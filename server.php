<?php

declare(strict_types=1);

use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\TickLoop;
use watermossmc\mcpe\PacketHandler;
use watermossmc\Server;
use watermossmc\util\Config;
use watermossmc\util\Logger;

require __DIR__ . '/vendor/autoload.php';

Config::load(__DIR__ . '/server.properties');

Logger::init();

// Configuration
$config = [
    'bind_ip' => Config::getString('server_ip', '0.0.0.0'),
    'bind_port' => Config::getInt('server_port', 19132),
    'max_players' => Config::getInt('max_players', 20),
    'motd' => Config::getString('motd', 'WatermossMC Server'),
];

$shutdown = false;

set_exception_handler(function (\Throwable $e) use (&$shutdown): void {
    Logger::error($e::class . ": " . $e->getMessage());

    foreach ($e->getTrace() as $i => $t) {
        $file = $t['file'] ?? 'unknown';
        $line = $t['line'] ?? 0;
        $func = $t['function'] ?? 'unknown';

        Logger::debug("#$i $file:$line ($func)");
    }
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
} else {
    Logger::warning('pcntl_signal not available; graceful SIGINT/SIGTERM handling disabled');
}

Logger::info("Starting WatermossMC server on {$config['bind_ip']}:{$config['bind_port']}");

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
PacketHandler::setServer($server);

$tickLoop->add(static function () use ($server): void {
    $server->tick();

    if ($server->getCurrentTick() % 20 === 0) {
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

    // Run tick loop
    $now = hrtime(true);
    if ($now >= $nextTick) {
        try {
            $tickLoop->runOnce();
            $nextTick += $tickInterval;
        } catch (\Throwable $e) {
            Logger::error("Error in tick loop: " . $e->getMessage());
        }
    }

    // Small sleep to prevent CPU hogging
    usleep(1000);
}

// Cleanup
Logger::info("Shutting down server...");
$server->shutdown();
socket_close($socket);
Logger::info("Server shutdown complete.");
