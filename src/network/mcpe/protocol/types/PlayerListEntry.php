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

namespace watermossmc\network\mcpe\protocol\types;

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\types\skin\SkinData;

class PlayerListEntry
{
	public UuidInterface $uuid;
	public int $actorUniqueId;
	public string $username;
	public SkinData $skinData;
	public string $xboxUserId;
	public string $platformChatId = "";
	public int $buildPlatform = DeviceOS::UNKNOWN;
	public bool $isTeacher = false;
	public bool $isHost = false;
	public bool $isSubClient = false;

	public static function createRemovalEntry(UuidInterface $uuid) : PlayerListEntry
	{
		$entry = new PlayerListEntry();
		$entry->uuid = $uuid;

		return $entry;
	}

	public static function createAdditionEntry(
		UuidInterface $uuid,
		int $actorUniqueId,
		string $username,
		SkinData $skinData,
		string $xboxUserId = "",
		string $platformChatId = "",
		int $buildPlatform = -1,
		bool $isTeacher = false,
		bool $isHost = false,
		bool $isSubClient = false
	) : PlayerListEntry {
		$entry = new PlayerListEntry();
		$entry->uuid = $uuid;
		$entry->actorUniqueId = $actorUniqueId;
		$entry->username = $username;
		$entry->skinData = $skinData;
		$entry->xboxUserId = $xboxUserId;
		$entry->platformChatId = $platformChatId;
		$entry->buildPlatform = $buildPlatform;
		$entry->isTeacher = $isTeacher;
		$entry->isHost = $isHost;
		$entry->isSubClient = $isSubClient;

		return $entry;
	}
}
