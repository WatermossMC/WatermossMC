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

namespace watermossmc\mcpe\network;

use RuntimeException;
use Socket;

final class UdpServer
{
    private Socket $socket;

    private bool $running = true;

    public function __construct(string $ip, int $port)
    {
        $sock = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        if ($sock === false) {
            throw new RuntimeException(
                'socket_create failed: ' . socket_strerror(socket_last_error())
            );
        }

        if (!socket_bind($sock, $ip, $port)) {
            throw new RuntimeException(
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
