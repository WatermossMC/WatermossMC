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

namespace watermossmc\event\entity;

use watermossmc\entity\Entity;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\utils\Utils;
use watermossmc\world\Position;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityTeleportEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Entity $entity,
		private Position $from,
		private Position $to
	) {
		$this->entity = $entity;
	}

	public function getFrom() : Position
	{
		return $this->from;
	}

	public function getTo() : Position
	{
		return $this->to;
	}

	public function setTo(Position $to) : void
	{
		Utils::checkVector3NotInfOrNaN($to);
		$this->to = $to;
	}
}
