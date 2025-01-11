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

namespace watermossmc\player;

use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\LongTag;

class OfflinePlayer implements IPlayer
{
	public function __construct(
		private string $name,
		private ?CompoundTag $namedtag
	) {
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getFirstPlayed() : ?int
	{
		return ($this->namedtag !== null && ($firstPlayedTag = $this->namedtag->getTag(Player::TAG_FIRST_PLAYED)) instanceof LongTag) ? $firstPlayedTag->getValue() : null;
	}

	public function getLastPlayed() : ?int
	{
		return ($this->namedtag !== null && ($lastPlayedTag = $this->namedtag->getTag(Player::TAG_LAST_PLAYED)) instanceof LongTag) ? $lastPlayedTag->getValue() : null;
	}

	public function hasPlayedBefore() : bool
	{
		return $this->namedtag !== null;
	}
}
