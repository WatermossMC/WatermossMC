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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackresponse;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;

use function count;

final class ItemStackResponseContainerInfo
{
	/**
	 * @param ItemStackResponseSlotInfo[] $slots
	 */
	public function __construct(
		private FullContainerName $containerName,
		private array $slots
	) {
	}

	public function getContainerName() : FullContainerName
	{
		return $this->containerName;
	}

	/** @return ItemStackResponseSlotInfo[] */
	public function getSlots() : array
	{
		return $this->slots;
	}

	public static function read(PacketSerializer $in) : self
	{
		$containerName = FullContainerName::read($in);
		$slots = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$slots[] = ItemStackResponseSlotInfo::read($in);
		}
		return new self($containerName, $slots);
	}

	public function write(PacketSerializer $out) : void
	{
		$this->containerName->write($out);
		$out->putUnsignedVarInt(count($this->slots));
		foreach ($this->slots as $slot) {
			$slot->write($out);
		}
	}
}
