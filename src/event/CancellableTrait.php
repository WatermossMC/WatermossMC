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

namespace watermossmc\event;

/**
 * This trait provides a basic boolean-setter-style implementation for `Cancellable` to reduce boilerplate.
 * The precise meaning of `setCancelled` is subject to definition by the class using this trait.
 *
 * Implementors of `Cancellable` are not required to use this trait.
 *
 * @see Cancellable
 */
trait CancellableTrait
{
	/** @var bool */
	private $isCancelled = false;

	public function isCancelled() : bool
	{
		return $this->isCancelled;
	}

	public function cancel() : void
	{
		$this->isCancelled = true;
	}

	public function uncancel() : void
	{
		$this->isCancelled = false;
	}
}
