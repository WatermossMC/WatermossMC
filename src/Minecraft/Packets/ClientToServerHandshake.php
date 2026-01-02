<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

final class ClientToServerHandshake extends Packet
{
    public static function read(string $buf, int &$o): void
    {
        //NOOP
    }
}
