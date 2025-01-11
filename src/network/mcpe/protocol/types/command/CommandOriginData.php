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

namespace watermossmc\network\mcpe\protocol\types\command;

use Ramsey\Uuid\UuidInterface;

class CommandOriginData
{
	public const ORIGIN_PLAYER = 0;
	public const ORIGIN_BLOCK = 1;
	public const ORIGIN_MINECART_BLOCK = 2;
	public const ORIGIN_DEV_CONSOLE = 3;
	public const ORIGIN_TEST = 4;
	public const ORIGIN_AUTOMATION_PLAYER = 5;
	public const ORIGIN_CLIENT_AUTOMATION = 6;
	public const ORIGIN_DEDICATED_SERVER = 7;
	public const ORIGIN_ENTITY = 8;
	public const ORIGIN_VIRTUAL = 9;
	public const ORIGIN_GAME_ARGUMENT = 10;
	public const ORIGIN_ENTITY_SERVER = 11; //???

	public int $type;
	public UuidInterface $uuid;
	public string $requestId;
	public int $playerActorUniqueId;
}
