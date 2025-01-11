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

final class EducationSettingsAgentCapabilities
{
	public function __construct(
		private ?bool $canModifyBlocks
	) {
	}

	public function getCanModifyBlocks() : ?bool
	{
		return $this->canModifyBlocks;
	}

	public static function read(PacketSerializer $in) : self
	{
		$canModifyBlocks = $in->readOptional($in->getBool(...));
		return new self($canModifyBlocks);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeOptional($this->canModifyBlocks, $out->putBool(...));
	}
}
