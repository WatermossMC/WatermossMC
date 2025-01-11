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

/**
 * Encapsulates player info specific to players who are authenticated with XBOX Live.
 */
final class XboxLivePlayerInfo extends PlayerInfo
{
	private string $xuid;

	public function __construct(string $xuid, string $username, UuidInterface $uuid, Skin $skin, string $locale, array $extraData = [])
	{
		parent::__construct($username, $uuid, $skin, $locale, $extraData);
		$this->xuid = $xuid;
	}

	public function getXuid() : string
	{
		return $this->xuid;
	}

	/**
	 * Returns a new PlayerInfo with XBL player info stripped. This is used to ensure that non-XBL players can't spoof
	 * XBL data.
	 */
	public function withoutXboxData() : PlayerInfo
	{
		return new PlayerInfo(
			$this->getUsername(),
			$this->getUuid(),
			$this->getSkin(),
			$this->getLocale(),
			$this->getExtraData()
		);
	}
}
