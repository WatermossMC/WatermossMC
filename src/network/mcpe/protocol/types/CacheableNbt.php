<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\nbt\tag\Tag;
use watermossmc\nbt\TreeRoot;
use watermossmc\network\mcpe\protocol\serializer\NetworkNbtSerializer;

/**
 * @phpstan-template TTagType of Tag
 */
final class CacheableNbt
{
	private ?string $encodedNbt = null;

	/**
	 * @phpstan-param TTagType $nbtRoot
	 */
	public function __construct(
		private Tag $nbtRoot
	) {
	}

	/**
	 * @phpstan-return TTagType
	 */
	public function getRoot() : Tag
	{
		return $this->nbtRoot;
	}

	public function getEncodedNbt() : string
	{
		return $this->encodedNbt ?? ($this->encodedNbt = (new NetworkNbtSerializer())->write(new TreeRoot($this->nbtRoot)));
	}
}
