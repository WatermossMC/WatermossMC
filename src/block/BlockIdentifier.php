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

namespace watermossmc\block;

use watermossmc\block\tile\Tile;
use watermossmc\utils\Utils;

class BlockIdentifier
{
	/**
	 * @phpstan-param class-string<Tile>|null $tileClass
	 */
	public function __construct(
		private int $blockTypeId,
		private ?string $tileClass = null
	) {
		if ($blockTypeId < 0) {
			throw new \InvalidArgumentException("Block type ID may not be negative");
		}
		if ($tileClass !== null) {
			Utils::testValidInstance($tileClass, Tile::class);
		}
	}

	public function getBlockTypeId() : int
	{
		return $this->blockTypeId;
	}

	/**
	 * @phpstan-return class-string<Tile>|null
	 */
	public function getTileClass() : ?string
	{
		return $this->tileClass;
	}
}
