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

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class NetworkPermissions
{
	public function __construct(
		private bool $disableClientSounds
	) {
	}

	public function disableClientSounds() : bool
	{
		return $this->disableClientSounds;
	}

	public static function decode(PacketSerializer $in) : self
	{
		$disableClientSounds = $in->getBool();
		return new self($disableClientSounds);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putBool($this->disableClientSounds);
	}
}
