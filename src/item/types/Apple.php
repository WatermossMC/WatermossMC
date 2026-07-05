<?php

declare(strict_types=1);

namespace watermossmc\item\types;

use watermossmc\item\Item;

final class Apple extends Item
{
    public function __construct()
    {
        parent::__construct(280, 'Apple', 'food');
    }
}
