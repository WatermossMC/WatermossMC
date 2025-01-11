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

trait DisappearStackRequestActionTrait
{
	final public function __construct(
		private int $count,
		private ItemStackRequestSlotInfo $source
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

	public static function read(PacketSerializer $in) : self
	{
		$count = $in->getByte();
		$source = ItemStackRequestSlotInfo::read($in);
		return new self($count, $source);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->count);
		$this->source->write($out);
	}
}
