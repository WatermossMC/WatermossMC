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

use watermossmc\event\Event;
use watermossmc\player\PlayerInfo;
use watermossmc\resourcepacks\ResourcePack;

use function array_unshift;

/**
 * Called after a player authenticates and is being offered resource packs to download.
 *
 * This event should be used to decide which resource packs to offer the player and whether to require the player to
 * download the packs before they can join the server.
 */
class PlayerResourcePackOfferEvent extends Event
{
	/**
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function __construct(
		private readonly PlayerInfo $playerInfo,
		private array $resourcePacks,
		private array $encryptionKeys,
		private bool $mustAccept
	) {
	}

	public function getPlayerInfo() : PlayerInfo
	{
		return $this->playerInfo;
	}

	/**
	 * Adds a resource pack to the top of the stack.
	 * The resources in this pack will be applied over the top of any existing packs.
	 */
	public function addResourcePack(ResourcePack $entry, ?string $encryptionKey = null) : void
	{
		array_unshift($this->resourcePacks, $entry);
		if ($encryptionKey !== null) {
			$this->encryptionKeys[$entry->getPackId()] = $encryptionKey;
		}
	}

	/**
	 * Sets the resource packs to offer. Packs are applied from the highest key to the lowest, with each pack
	 * overwriting any resources from the previous pack. This means that the pack at index 0 gets the final say on which
	 * resources are used.
	 *
	 * @param ResourcePack[] $resourcePacks
	 * @param string[]       $encryptionKeys pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePacks
	 * @phpstan-param array<string, string> $encryptionKeys
	 */
	public function setResourcePacks(array $resourcePacks, array $encryptionKeys) : void
	{
		$this->resourcePacks = $resourcePacks;
		$this->encryptionKeys = $encryptionKeys;
	}

	/**
	 * @return ResourcePack[]
	 * @phpstan-return list<ResourcePack>
	 */
	public function getResourcePacks() : array
	{
		return $this->resourcePacks;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getEncryptionKeys() : array
	{
		return $this->encryptionKeys;
	}

	public function setMustAccept(bool $mustAccept) : void
	{
		$this->mustAccept = $mustAccept;
	}

	public function mustAccept() : bool
	{
		return $this->mustAccept;
	}
}
