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

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class Enchant
{
	public function __construct(
		private int $id,
		private int $level
	) {
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getLevel() : int
	{
		return $this->level;
	}

	public static function read(PacketSerializer $in) : self
	{
		$id = $in->getByte();
		$level = $in->getByte();
		return new self($id, $level);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->id);
		$out->putByte($this->level);
	}
}
