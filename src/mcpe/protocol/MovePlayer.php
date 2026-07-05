<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\types\MovePlayerMode;
use watermossmc\player\PlayerManager;

final class MovePlayer extends Packet
{
    /**
     * @return array{x: float, y: float, z: float, pitch: float, yaw: float, headYaw: float, onGround: bool}
     */
    public static function read(string $p, int &$o): array
    {
        $o++; // skip PID
        Binary::readLong($p, $o); // skip runtimeId

        $x = Binary::readFloat($p, $o);
        $y = Binary::readFloat($p, $o);
        $z = Binary::readFloat($p, $o);

        $pitch = Binary::readFloat($p, $o);
        $yaw = Binary::readFloat($p, $o);
        $headYaw = Binary::readFloat($p, $o);

        $mode = Binary::readByte($p, $o);
        $onGround = Binary::readBool($p, $o);

        Binary::readLong($p, $o); // skip something

        if ($mode === MovePlayerMode::TELEPORT) {
            Binary::readInt($p, $o);
            Binary::readInt($p, $o);
        }

        Binary::readVarLong($p, $o); // skip something

        return [
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'pitch' => $pitch,
            'yaw' => $yaw,
            'headYaw' => $headYaw,
            'onGround' => $onGround,
        ];
    }

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
