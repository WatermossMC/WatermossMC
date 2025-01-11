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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class PlayerAuthInputVehicleInfo
{
	public function __construct(
		private float $vehicleRotationX,
		private float $vehicleRotationZ,
		private int $predictedVehicleActorUniqueId
	) {
	}

	public function getVehicleRotationX() : float
	{
		return $this->vehicleRotationX;
	}

	public function getVehicleRotationZ() : float
	{
		return $this->vehicleRotationZ;
	}

	public function getPredictedVehicleActorUniqueId() : int
	{
		return $this->predictedVehicleActorUniqueId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$vehicleRotationX = $in->getLFloat();
		$vehicleRotationZ = $in->getLFloat();
		$predictedVehicleActorUniqueId = $in->getActorUniqueId();

		return new self($vehicleRotationX, $vehicleRotationZ, $predictedVehicleActorUniqueId);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->vehicleRotationX);
		$out->putLFloat($this->vehicleRotationZ);
		$out->putActorUniqueId($this->predictedVehicleActorUniqueId);
	}
}
