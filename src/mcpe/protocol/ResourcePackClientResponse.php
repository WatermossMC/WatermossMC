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

declare (strict_types=1);

namespace watermossmc\mcpe\protocol;

use watermossmc\binary\Binary;

final class ResourcePackClientResponse extends Packet
{
    public const STATUS_REFUSED = 1;
    public const STATUS_SEND_PACKS = 2;
    public const STATUS_HAVE_ALL_PACKS = 3;
    public const STATUS_COMPLETED = 4;

    /**
     * @return array{status:int}
     */
    public static function read(string $p, int &$o): array
    {
        $status = Binary::readByte($p, $o);
        $count = Binary::readLShort($p, $o);
        $packs = [];
        for ($i = 0; $i < $count; $i++) {
            $packs[] = Binary::readString($p, $o);
        }
        return ['status' => $status, 'packs' => $packs];
    }
}
