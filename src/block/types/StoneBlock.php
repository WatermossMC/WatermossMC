<?php

declare(strict_types=1);

namespace watermossmc\block\types;

use watermossmc\block\Block;

final class StoneBlock extends Block
{
    public function __construct()
    {
        parent::__construct(1, 'Stone');
    }
}
