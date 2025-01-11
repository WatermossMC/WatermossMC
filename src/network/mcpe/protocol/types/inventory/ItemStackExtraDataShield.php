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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\nbt\tag\CompoundTag;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

/**
 * Extension of ItemStackExtraData for shield items, which have an additional field for the blocking tick.
 */
final class ItemStackExtraDataShield extends ItemStackExtraData
{
	/**
	 * @param string[] $canPlaceOn
	 * @param string[] $canDestroy
	 */
	public function __construct(
		?CompoundTag $nbt,
		array $canPlaceOn,
		array $canDestroy,
		private int $blockingTick
	) {
		parent::__construct($nbt, $canPlaceOn, $canDestroy);
	}

	public function getBlockingTick() : int
	{
		return $this->blockingTick;
	}

	public static function read(PacketSerializer $in) : self
	{
		$base = parent::read($in);
		$blockingTick = $in->getLLong();

		return new self($base->getNbt(), $base->getCanPlaceOn(), $base->getCanDestroy(), $blockingTick);
	}

	public function write(PacketSerializer $out) : void
	{
		parent::write($out);
		$out->putLLong($this->blockingTick);
	}
}
