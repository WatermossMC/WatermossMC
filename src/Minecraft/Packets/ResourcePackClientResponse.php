<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use WatermossMC\Binary\Binary;

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
        $count = Binary::readShort($p, $o);

        $packs = [];
        for ($i = 0; $i < $count; $i++) {
            $packs[] = Binary::readStringInt($p, $o);
        }

        return [
            'status' => $status,
            'packs' => $packs,
        ];
    }
}
