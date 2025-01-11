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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraTargetInstruction
{
	public function __construct(
		private ?Vector3 $targetCenterOffset,
		private int $actorUniqueId
	) {
	}

	public function getTargetCenterOffset() : ?Vector3
	{
		return $this->targetCenterOffset;
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$targetCenterOffset = $in->readOptional(fn () => $in->getVector3());
		$actorUniqueId = $in->getLLong(); //why be consistent mojang ?????
		return new self(
			$targetCenterOffset,
			$actorUniqueId
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeOptional($this->targetCenterOffset, fn (Vector3 $v) => $out->putVector3($v));
		$out->putLLong($this->actorUniqueId);
	}
}
