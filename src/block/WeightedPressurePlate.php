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

namespace watermossmc\block;

use watermossmc\block\utils\AnalogRedstoneSignalEmitterTrait;

use function ceil;
use function count;
use function max;
use function min;

class WeightedPressurePlate extends PressurePlate
{
	use AnalogRedstoneSignalEmitterTrait;

	private readonly float $signalStrengthFactor;

	/**
	 * @param float $signalStrengthFactor Number of entities on the plate is divided by this value to get signal strength
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $deactivationDelayTicks, float $signalStrengthFactor = 1.0)
	{
		parent::__construct($idInfo, $name, $typeInfo, $deactivationDelayTicks);
		$this->signalStrengthFactor = $signalStrengthFactor;
	}

	protected function hasOutputSignal() : bool
	{
		return $this->signalStrength > 0;
	}

	protected function calculatePlateState(array $entities) : array
	{
		$newSignalStrength = min(15, max(
			0,
			(int) ceil(count($entities) * $this->signalStrengthFactor)
		));
		if ($newSignalStrength === $this->signalStrength) {
			return [$this, null];
		}
		$wasActive = $this->signalStrength !== 0;
		$isActive = $newSignalStrength !== 0;
		return [
			(clone $this)->setOutputSignalStrength($newSignalStrength),
			$wasActive !== $isActive ? $isActive : null
		];
	}
}
