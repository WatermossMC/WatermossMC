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

use watermossmc\item\Item;

final class InfestedStone extends Opaque
{
	private int $imitated;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, Block $imitated)
	{
		parent::__construct($idInfo, $name, $typeInfo);
		$this->imitated = $imitated->getStateId();
	}

	public function getImitatedBlock() : Block
	{
		return RuntimeBlockStateRegistry::getInstance()->fromStateId($this->imitated);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function getSilkTouchDrops(Item $item) : array
	{
		return [$this->getImitatedBlock()->asItem()];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	//TODO
}
