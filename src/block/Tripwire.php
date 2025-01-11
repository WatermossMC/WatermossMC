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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

class Tripwire extends Flowable
{
	protected bool $triggered = false;
	protected bool $suspended = false; //unclear usage, makes hitbox bigger if set
	protected bool $connected = false;
	protected bool $disarmed = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->triggered);
		$w->bool($this->suspended);
		$w->bool($this->connected);
		$w->bool($this->disarmed);
	}

	public function isTriggered() : bool
	{
		return $this->triggered;
	}

	/** @return $this */
	public function setTriggered(bool $triggered) : self
	{
		$this->triggered = $triggered;
		return $this;
	}

	public function isSuspended() : bool
	{
		return $this->suspended;
	}

	/** @return $this */
	public function setSuspended(bool $suspended) : self
	{
		$this->suspended = $suspended;
		return $this;
	}

	public function isConnected() : bool
	{
		return $this->connected;
	}

	/** @return $this */
	public function setConnected(bool $connected) : self
	{
		$this->connected = $connected;
		return $this;
	}

	public function isDisarmed() : bool
	{
		return $this->disarmed;
	}

	/** @return $this */
	public function setDisarmed(bool $disarmed) : self
	{
		$this->disarmed = $disarmed;
		return $this;
	}

	public function asItem() : Item
	{
		return VanillaItems::STRING();
	}
}
