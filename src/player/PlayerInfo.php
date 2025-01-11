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
