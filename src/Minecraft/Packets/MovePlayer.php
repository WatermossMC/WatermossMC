<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Packets\Types\MovePlayerMode;
use WatermossMC\Minecraft\PlayerManager;
use WatermossMC\Network\Session;

final class MovePlayer extends Packet
{
    public static function handle(string $p, Session $s): void
    {
        $o = 1;


        Binary::readLong($p, $o);


        $x = Binary::readFloat($p, $o);
        $y = Binary::readFloat($p, $o);
        $z = Binary::readFloat($p, $o);


        $pitch = Binary::readFloat($p, $o);
        $yaw = Binary::readFloat($p, $o);
        $headYaw = Binary::readFloat($p, $o);

        $mode = Binary::readByte($p, $o);
        $onGround = Binary::readBool($p, $o);


        Binary::readLong($p, $o);

        if ($mode === MovePlayerMode::TELEPORT) {
            Binary::readInt($p, $o);
            Binary::readInt($p, $o);
        }

        Binary::readVarLong($p, $o);

        $player = PlayerManager::get($s);
        if ($player === null) {
            return;
        }

        $player->pendingMove = [
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'yaw' => $yaw,
            'pitch' => $pitch,
            'headYaw' => $headYaw,
            'onGround' => $onGround,
        ];
    }
}
