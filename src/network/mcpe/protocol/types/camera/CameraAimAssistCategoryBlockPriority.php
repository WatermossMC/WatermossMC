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

namespace watermossmc\network\mcpe\protocol\types\camera;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraAimAssistCategoryBlockPriority
{
	public function __construct(
		private string $identifier,
		private int $priority
	) {
	}

	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public static function read(PacketSerializer $in) : self
	{
		$identifier = $in->getString();
		$priority = $in->getLInt();
		return new self(
			$identifier,
			$priority
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->identifier);
		$out->putLInt($this->priority);
	}
}
