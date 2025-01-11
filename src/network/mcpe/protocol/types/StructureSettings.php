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

use watermossmc\math\Vector3;

class StructureSettings
{
	public string $paletteName;
	public bool $ignoreEntities;
	public bool $ignoreBlocks;
	public bool $allowNonTickingChunks;
	public BlockPosition $dimensions;
	public BlockPosition $offset;
	public int $lastTouchedByPlayerID;
	public int $rotation;
	public int $mirror;
	public int $animationMode;
	public float $animationSeconds;
	public float $integrityValue;
	public int $integritySeed;
	public Vector3 $pivot;
}
