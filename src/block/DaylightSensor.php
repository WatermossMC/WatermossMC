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
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function cos;
use function max;
use function round;

use const M_PI;

class DaylightSensor extends Transparent
{
	use AnalogRedstoneSignalEmitterTrait;

	protected bool $inverted = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, 15, $this->signalStrength);
		$w->bool($this->inverted);
	}

	public function isInverted() : bool
	{
		return $this->inverted;
	}

	/**
	 * @return $this
	 */
	public function setInverted(bool $inverted = true) : self
	{
		$this->inverted = $inverted;
		return $this;
	}

	public function getFuelTime() : int
	{
		return 300;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->trim(Facing::UP, 10 / 16)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->inverted = !$this->inverted;
		$this->signalStrength = $this->recalculateSignalStrength();
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function onScheduledUpdate() : void
	{
		$world = $this->position->getWorld();
		$signalStrength = $this->recalculateSignalStrength();
		if ($this->signalStrength !== $signalStrength) {
			$this->signalStrength = $signalStrength;
			$world->setBlock($this->position, $this);
		}
		$world->scheduleDelayedBlockUpdate($this->position, 20);
	}

	private function recalculateSignalStrength() : int
	{
		$world = $this->position->getWorld();
		$lightLevel = $world->getRealBlockSkyLightAt($this->position->x, $this->position->y, $this->position->z);
		if ($this->inverted) {
			return 15 - $lightLevel;
		}

		$sunAngle = $world->getSunAnglePercentage();
		return max(0, (int) round($lightLevel * cos(($sunAngle + ((($sunAngle < 0.5 ? 0 : 1) - $sunAngle) / 5)) * 2 * M_PI)));
	}

	//TODO
}
