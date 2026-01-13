<?php

declare(strict_types=1);

use WatermossMC\Network\RakNet;
use WatermossMC\Network\TickLoop;
use WatermossMC\Util\Logger;

require __DIR__ . '/vendor/autoload.php';

// Initialize logger
\WatermossMC\Util\Logger::init();

// Configuration
$config = [
    'bind_ip' => getenv('SERVER_IP') ?: '0.0.0.0',
    'bind_port' => (int) (getenv('SERVER_PORT') ?: 19132),
    'max_players' => (int) (getenv('MAX_PLAYERS') ?: 20),
    'motd' => getenv('MOTD') ?: 'WatermossMC Server',
];

$shutdown = false;

set_exception_handler(function (\Throwable $e): void {
    Logger::error($e::class . ": " . $e->getMessage());

    foreach ($e->getTrace() as $i => $t) {
        $file = $t['file'] ?? 'unknown';
        $line = $t['line'] ?? 0;
        $func = $t['function'] ?? 'unknown';

        Logger::debug("#$i $file:$line ($func)");
    }
});

// Signal handlers for graceful shutdown
pcntl_signal(SIGTERM, function () use (&$shutdown): void {
    Logger::info("Received SIGTERM, shutting down gracefully...");
    $shutdown = true;
});

pcntl_signal(SIGINT, function () use (&$shutdown): void {
    Logger::info("Received SIGINT, shutting down gracefully...");
    $shutdown = true;
});

Logger::info("Starting WatermossMC server on {$config['bind_ip']}:{$config['bind_port']}");

// Create UDP socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($socket === false) {
    $error = socket_strerror(socket_last_error());
    Logger::error("Failed to create UDP socket: $error");
    exit(1);
}

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_set_nonblock($socket);

if (!socket_bind($socket, $config['bind_ip'], $config['bind_port'])) {
    $error = socket_strerror(socket_last_error());
    Logger::error("Failed to bind {$config['bind_ip']}:{$config['bind_port']}: $error");
    socket_close($socket);
    exit(1);
}

RakNet::init();
Logger::info("RakNet initialized");

// Initialize tick loop with actual logic
$tickLoop = new TickLoop();
$tickCount = 0;

$tickLoop->add(function () use (&$tickCount): void {
    $tickCount++;
    // Basic heartbeat every 20 ticks (1 second at 20 TPS)
    if ($tickCount % 20 === 0) {
        Logger::debug("Server heartbeat - Tick: $tickCount");
    }
});

Logger::info("Server started successfully. Press Ctrl+C to stop.");


$buffer = '';
$fromIp = '';
$fromPort = 0;

$nextTick = hrtime(true);
$tickInterval = 1_000_000_000 / 20; // 20 TPS

Logger::info("Entering main server loop...");

while (!$shutdown) {
    // Handle signals
    pcntl_signal_dispatch();

    // Process incoming packets
    while (!$shutdown && @socket_recvfrom(
        $socket,
        $buffer,
        65535,
        MSG_DONTWAIT,
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
socket_close($socket);
Logger::info("Server shutdown complete.");
