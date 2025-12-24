<?php


declare(strict_types=1);

namespace WatermossMC\Minecraft\World;

use WatermossMC\Binary\Binary;

final class SubChunk
{
    private const SIZE = 4096;

    /** @var int[] */
    private array $blocks = [];

    public function __construct()
    {
        $this->blocks = array_fill(0, self::SIZE, 0);
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $index = ($y << 8) | ($z << 4) | $x;
        $this->blocks[$index] = $id;
    }

    public function encode(): string
    {
        // version
        $out = Binary::writeByte(8);

        $palette = array_values(array_unique($this->blocks));
        $bits = max(1, (int)ceil(log(\count($palette), 2)));

        $out .= Binary::writeByte($bits);

        // block data
        $out .= $this->encodeBlocks($palette, $bits);

        // palette entries
        $out .= Binary::writeVarInt(\count($palette));
        foreach ($palette as $id) {
            $out .= Binary::writeVarInt($id);
        }

        return $out;
    }

    /**
     * @param array<int, int> $palette
     */
    private function encodeBlocks(array $palette, int $bits): string
    {
        $indexes = array_flip($palette);

        $buffer = '';
        $value = 0;
        $bitPos = 0;

        foreach ($this->blocks as $block) {
            $value |= ($indexes[$block] << $bitPos);
            $bitPos += $bits;

            if ($bitPos >= 32) {
                $buffer .= Binary::writeInt($value);
                $value = 0;
                $bitPos = 0;
            }
        }

        if ($bitPos > 0) {
            $buffer .= Binary::writeInt($value);
        }

        return $buffer;
    }
}
