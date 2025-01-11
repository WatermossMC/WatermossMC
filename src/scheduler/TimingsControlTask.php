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

namespace watermossmc\scheduler;

use watermossmc\timings\TimingsHandler;

final class TimingsControlTask extends AsyncTask
{
	private const ENABLE = 1;
	private const DISABLE = 2;
	private const RELOAD = 3;

	private function __construct(
		private int $operation
	) {
	}

	public static function setEnabled(bool $enable) : self
	{
		return new self($enable ? self::ENABLE : self::DISABLE);
	}

	public static function reload() : self
	{
		return new self(self::RELOAD);
	}

	public function onRun() : void
	{
		if ($this->operation === self::ENABLE) {
			TimingsHandler::setEnabled(true);
			\GlobalLogger::get()->debug("Enabled timings");
		} elseif ($this->operation === self::DISABLE) {
			TimingsHandler::setEnabled(false);
			\GlobalLogger::get()->debug("Disabled timings");
		} elseif ($this->operation === self::RELOAD) {
			TimingsHandler::reload();
			\GlobalLogger::get()->debug("Reset timings");
		} else {
			throw new \InvalidArgumentException("Invalid operation $this->operation");
		}
	}
}
