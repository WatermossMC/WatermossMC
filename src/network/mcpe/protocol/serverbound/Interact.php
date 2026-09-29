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

namespace watermossmc\network\mcpe\protocol\serverbound;

use watermossmc\binary\McpeBinary;

final class Interact
{
    public const ACTION_LEAVE_VEHICLE = 3;
    public const ACTION_MOUSEOVER = 4;
    public const ACTION_OPEN_NPC = 5;
    public const ACTION_OPEN_INVENTORY = 6;

    public static function read(string $p, int &$o): array
    {
        $action = McpeBinary::readUnsignedByte($p, $o);
        $target = McpeBinary::readUnsignedVarLong($p, $o);

        $position = null;
        if (McpeBinary::readBool($p, $o)) {
            $position = [
                McpeBinary::readFloat($p, $o),
                McpeBinary::readFloat($p, $o),
                McpeBinary::readFloat($p, $o),
            ];
        }

        return ['action' => $action, 'targetActorRuntimeId' => $target, 'position' => $position];
    }
}
