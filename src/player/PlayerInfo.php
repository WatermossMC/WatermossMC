<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\player;

use Ramsey\Uuid\UuidInterface;
use watermossmc\entity\Skin;
use watermossmc\utils\TextFormat;

/**
 * Encapsulates data needed to create a player.
 */
class PlayerInfo
{
	/**
	 * @param mixed[] $extraData
	 * @phpstan-param array<string, mixed> $extraData
	 */
	public function __construct(
		private string $username,
		private UuidInterface $uuid,
		private Skin $skin,
		private string $locale,
		private array $extraData = []
	) {
		$this->username = TextFormat::clean($username);
	}

	public function getUsername() : string
	{
		return $this->username;
	}

	public function getUuid() : UuidInterface
	{
		return $this->uuid;
	}

	public function getSkin() : Skin
	{
		return $this->skin;
	}

	public function getLocale() : string
	{
		return $this->locale;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getExtraData() : array
	{
		return $this->extraData;
	}
}
