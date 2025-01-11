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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackrequest;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Creates an item by copying it from the creative inventory. This is treated as a crafting action by vanilla.
 */
final class CreativeCreateStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CREATIVE_CREATE;

	public function __construct(
		private int $creativeItemId,
		private int $repetitions
	) {
	}

	public function getCreativeItemId() : int
	{
		return $this->creativeItemId;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public static function read(PacketSerializer $in) : self
	{
		$creativeItemId = $in->readCreativeItemNetId();
		$repetitions = $in->getByte();
		return new self($creativeItemId, $repetitions);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeCreativeItemNetId($this->creativeItemId);
		$out->putByte($this->repetitions);
	}
}
