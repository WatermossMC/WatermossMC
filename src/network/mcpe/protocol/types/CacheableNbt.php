<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
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
