<?php

declare(strict_types=1);

namespace WatermossMC\Network;

use Socket;

final class UdpServer
{
    private Socket $socket;

    private bool $running = true;

    public function __construct(string $ip, int $port)
    {
        $sock = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        if ($sock === false) {
            throw new \RuntimeException(
                'socket_create failed: ' . socket_strerror(socket_last_error())
            );
        }

        if (!socket_bind($sock, $ip, $port)) {
            throw new \RuntimeException(
                'socket_bind failed: ' . socket_strerror(socket_last_error($sock))
            );
        }

        $this->socket = $sock;
    }

    public function run(callable $handler): void
    {
        while ($this->running) {
            socket_recvfrom($this->socket, $buf, 2048, 0, $addr, $port);
            $handler($buf, $addr, $port, $this->socket);
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }
}
