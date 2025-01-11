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

final class ItemComponentPacketEntry
{
	/**
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $componentNbt
	 */
	public function __construct(
		private string $name,
		private CacheableNbt $componentNbt
	) {
	}

	public function getName() : string
	{
		return $this->name;
	}

	/** @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public function getComponentNbt() : CacheableNbt
	{
		return $this->componentNbt;
	}
}
