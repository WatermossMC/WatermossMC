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

class ScorePacketEntry
{
	public const TYPE_PLAYER = 1;
	public const TYPE_ENTITY = 2;
	public const TYPE_FAKE_PLAYER = 3;

	public int $scoreboardId;
	public string $objectiveName;
	public int $score;
	public int $type;
	/** @var int|null (if type entity or player) */
	public ?int $actorUniqueId;
	/** @var string|null (if type fake player) */
	public ?string $customName;
}
