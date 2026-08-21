<?php

declare(strict_types=1);

namespace watermossmc\block;

interface BlockStateProvider
{
	public function getRuntimeId(BlockState $state): int;
}