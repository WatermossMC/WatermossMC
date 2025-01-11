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

final class SubChunkPacketEntryWithoutCache
{
	public function __construct(
		private SubChunkPacketEntryCommon $base
	) {
	}

	public function getBase() : SubChunkPacketEntryCommon
	{
		return $this->base;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self(SubChunkPacketEntryCommon::read($in, false));
	}

	public function write(PacketSerializer $out) : void
	{
		$this->base->write($out, false);
	}
}
