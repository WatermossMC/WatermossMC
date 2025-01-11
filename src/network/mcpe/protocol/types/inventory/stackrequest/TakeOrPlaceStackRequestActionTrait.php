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

trait TakeOrPlaceStackRequestActionTrait
{
	final public function __construct(
		private int $count,
		private ItemStackRequestSlotInfo $source,
		private ItemStackRequestSlotInfo $destination
	) {
	}

	final public function getCount() : int
	{
		return $this->count;
	}

	final public function getSource() : ItemStackRequestSlotInfo
	{
		return $this->source;
	}

	final public function getDestination() : ItemStackRequestSlotInfo
	{
		return $this->destination;
	}

	public static function read(PacketSerializer $in) : self
	{
		$count = $in->getByte();
		$src = ItemStackRequestSlotInfo::read($in);
		$dst = ItemStackRequestSlotInfo::read($in);
		return new self($count, $src, $dst);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->count);
		$this->source->write($out);
		$this->destination->write($out);
	}
}
