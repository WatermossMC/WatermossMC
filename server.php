<?php

declare(strict_types=1);

use WatermossMC\Network\RakNet;
use WatermossMC\Network\TickLoop;
use WatermossMC\Util\Logger;

require __DIR__ . '/vendor/autoload.php';

$bindIp = "0.0.0.0";
$bindPort = 19132;


set_exception_handler(function (\Throwable $e): void {
    Logger::error($e::class . ": " . $e->getMessage());

    foreach ($e->getTrace() as $i => $t) {
        $file = $t['file'] ?? 'unknown';
        $line = $t['line'] ?? 0;
        $func = $t['function'] ?? 'unknown';

        Logger::debug("#$i $file:$line ($func)");
    }
});


Logger::info("Starting WatermossMC server");


$socket = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
if ($socket === false) {
    Logger::error("Failed to create UDP socket");
    exit(1);
}

socket_set_option($socket, \SOL_SOCKET, \SO_REUSEADDR, 1);
socket_set_nonblock($socket);

if (!socket_bind($socket, $bindIp, $bindPort)) {
    Logger::error("Failed to bind {$bindIp}:{$bindPort}");
    exit(1);
}


RakNet::init();
Logger::info("RakNet initialized");


$tickLoop = new TickLoop();

$tickLoop->add(function (): void {});

Logger::info("Started on {$bindIp}:{$bindPort}");


$buffer = '';
$fromIp = '';
$fromPort = 0;

$nextTick = hrtime(true);
$tickInterval = 1_000_000_000 / 20;

while (true) {

    while (@socket_recvfrom(
        $socket,
        $buffer,
        65535,
        \MSG_DONTWAIT,
        $fromIp,
        $fromPort
    )) {
        RakNet::handle($buffer, $fromIp, $fromPort, $socket);
        $buffer = '';
    }

    $now = hrtime(true);
    if ($now >= $nextTick) {
        $tickLoop->runOnce();
        $nextTick += $tickInterval;
    }

    usleep(1000);
}
