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

namespace watermossmc\event\world;

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\particle\Particle;
use watermossmc\world\World;

class WorldParticleEvent extends WorldEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param Player[] $recipients
	 */
	public function __construct(
		World $world,
		private Particle $particle,
		private Vector3 $position,
		private array $recipients
	) {
		parent::__construct($world);
	}

	public function getParticle() : Particle
	{
		return $this->particle;
	}

	public function setParticle(Particle $particle) : void
	{
		$this->particle = $particle;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	/**
	 * @return Player[]
	 */
	public function getRecipients() : array
	{
		return $this->recipients;
	}

	/**
	 * @param Player[] $recipients
	 */
	public function setRecipients(array $recipients) : void
	{
		$this->recipients = $recipients;
	}
}
