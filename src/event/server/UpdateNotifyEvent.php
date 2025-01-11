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

namespace watermossmc\event\server;

use watermossmc\updater\UpdateChecker;

/**
 * Called when the update checker receives notification of an available WatermossMC update.
 * Plugins may use this event to perform actions when an update notification is received.
 */
class UpdateNotifyEvent extends ServerEvent
{
	public function __construct(private UpdateChecker $updater)
	{
	}

	public function getUpdater() : UpdateChecker
	{
		return $this->updater;
	}
}
