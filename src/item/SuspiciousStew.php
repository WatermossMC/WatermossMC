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

namespace watermossmc\item;

use watermossmc\data\runtime\RuntimeDataDescriber;

class SuspiciousStew extends Food
{
	private SuspiciousStewType $suspiciousStewType = SuspiciousStewType::POPPY;

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->suspiciousStewType);
	}

	public function getType() : SuspiciousStewType
	{
		return $this->suspiciousStewType;
	}

	/**
	 * @return $this
	 */
	public function setType(SuspiciousStewType $type) : self
	{
		$this->suspiciousStewType = $type;
		return $this;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function requiresHunger() : bool
	{
		return false;
	}

	public function getFoodRestore() : int
	{
		return 6;
	}

	public function getSaturationRestore() : float
	{
		return 7.2;
	}

	public function getAdditionalEffects() : array
	{
		return $this->suspiciousStewType->getEffects();
	}

	public function getResidue() : Item
	{
		return VanillaItems::BOWL();
	}
}
