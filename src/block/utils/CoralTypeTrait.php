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

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\data\runtime\RuntimeDataDescriber;

trait CoralTypeTrait
{
	protected CoralType $coralType = CoralType::TUBE;
	protected bool $dead = false;

	/** @see Block::describeBlockItemState() */
	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->coralType);
		$w->bool($this->dead);
	}

	public function getCoralType() : CoralType
	{
		return $this->coralType;
	}

	/** @return $this */
	public function setCoralType(CoralType $coralType) : self
	{
		$this->coralType = $coralType;
		return $this;
	}

	public function isDead() : bool
	{
		return $this->dead;
	}

	/** @return $this */
	public function setDead(bool $dead) : self
	{
		$this->dead = $dead;
		return $this;
	}
}
