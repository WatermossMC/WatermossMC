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

namespace watermossmc\event\player;

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\lang\Translatable;
use watermossmc\network\mcpe\NetworkSession;

/**
 * Called when a player connects with a username or UUID that is already used by another player on the server.
 * If cancelled, the newly connecting session will be disconnected; otherwise, the existing player will be disconnected.
 */
class PlayerDuplicateLoginEvent extends Event implements Cancellable
{
	use CancellableTrait;
	use PlayerDisconnectEventTrait;

	public function __construct(
		private NetworkSession $connectingSession,
		private NetworkSession $existingSession,
		private Translatable|string $disconnectReason,
		private Translatable|string|null $disconnectScreenMessage
	) {
	}

	public function getConnectingSession() : NetworkSession
	{
		return $this->connectingSession;
	}

	public function getExistingSession() : NetworkSession
	{
		return $this->existingSession;
	}
}
