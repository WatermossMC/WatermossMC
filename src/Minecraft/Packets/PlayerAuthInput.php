<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Packets\Types\PlayerAuthInputFlags;
use WatermossMC\Minecraft\PlayerManager;
use WatermossMC\Network\Session;

final class PlayerAuthInput extends Packet
{
    public static function handle(string $p, Session $s): void
    {
        $o = 1;

        $pitch = Binary::readFloat($p, $o);
        $o += 4;
        $yaw = Binary::readFloat($p, $o);
        $o += 4;

        $x = Binary::readFloat($p, $o);
        $o += 4;
        $y = Binary::readFloat($p, $o);
        $o += 4;
        $z = Binary::readFloat($p, $o);
        $o += 4;

        $moveX = Binary::readFloat($p, $o);
        $o += 4;
        $moveZ = Binary::readFloat($p, $o);
        $o += 4;

        $headYaw = Binary::readFloat($p, $o);
        $o += 4;

        // BitSet (input flags)
        $flags = Binary::readBitSet($p, $o, 65); // PlayerAuthInputFlags::NUMBER_OF_FLAGS

        Binary::readVarInt($p, $o); // inputMode
        Binary::readVarInt($p, $o); // playMode
        Binary::readVarInt($p, $o); // interactionMode

        Binary::readVector2($p, $o); // interactRotation
        $tick = Binary::readVarLong($p, $o);

        Binary::readVector3($p, $o); // delta

        if ($flags[PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION]) {
            Binary::skipItemInteractionData($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST]) {
            Binary::skipItemStackRequest($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS]) {
            Binary::skipBlockActions($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE]) {
            Binary::skipVehicleInfo($p, $o);
        }

        $analogX = Binary::readFloat($p, $o);
        $o += 4;
        $analogZ = Binary::readFloat($p, $o);
        $o += 4;

        Binary::readVector3($p, $o); // cameraOrientation
        Binary::readVector2($p, $o); // rawMove

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
            'tick' => $tick,
        ];
    }
}
