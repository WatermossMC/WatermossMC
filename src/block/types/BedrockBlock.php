<?php

declare(strict_types=1);

namespace watermossmc\block\types;

use watermossmc\block\Block;

final class BedrockBlock extends Block
{
    public function __construct()
    {
        parent::__construct(7, 'Bedrock');
    }

    public function onBreak(int $x, int $y, int $z): void
    {
        // Bedrock cannot be broken in survival
    }
}
