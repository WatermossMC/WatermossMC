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

use watermossmc\binary\McpeBinary;

final class ResourcePackClientResponse extends Packet
{
    public const STATUS_REFUSED = 0;
    public const STATUS_SEND_PACKS = 1;
    public const STATUS_HAVE_ALL_PACKS = 2;
    public const STATUS_COMPLETED = 3;

    /**
     * @return array{status:int}
     */
    public static function read(string $p, int &$o): array
    {
        $status = McpeBinary::readVarInt($p, $o);
        McpeBinary::readString($p, $o);

        $packs = [];
        if($status === self::STATUS_SEND_PACKS){
          $count = McpeBinary::readVarInt($p, $o);
          for ($i = 0; $i < $count; $i++) {
            $packs[] = McpeBinary::readString($p, $o);
          }
        }
        return ['status' => $status, 'packs' => $packs];
    }
}
