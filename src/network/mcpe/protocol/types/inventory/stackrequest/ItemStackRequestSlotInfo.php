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
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;

final class ItemStackRequestSlotInfo
{
	public function __construct(
		private FullContainerName $containerName,
		private int $slotId,
		private int $stackId
	) {
	}

	public function getContainerName() : FullContainerName
	{
		return $this->containerName;
	}

	public function getSlotId() : int
	{
		return $this->slotId;
	}

	public function getStackId() : int
	{
		return $this->stackId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$containerName = FullContainerName::read($in);
		$slotId = $in->getByte();
		$stackId = $in->readItemStackNetIdVariant();
		return new self($containerName, $slotId, $stackId);
	}

	public function write(PacketSerializer $out) : void
	{
		$this->containerName->write($out);
		$out->putByte($this->slotId);
		$out->writeItemStackNetIdVariant($this->stackId);
	}
}
