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

final class CameraAimAssistCategory
{
	public function __construct(
		private string $name,
		private CameraAimAssistCategoryPriorities $priorities
	) {
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getPriorities() : CameraAimAssistCategoryPriorities
	{
		return $this->priorities;
	}

	public static function read(PacketSerializer $in) : self
	{
		$name = $in->getString();
		$priorities = CameraAimAssistCategoryPriorities::read($in);
		return new self(
			$name,
			$priorities
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->name);
		$this->priorities->write($out);
	}
}
