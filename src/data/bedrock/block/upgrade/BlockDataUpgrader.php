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

namespace watermossmc\data\bedrock\block\upgrade;

use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\nbt\tag\CompoundTag;

final class BlockDataUpgrader
{
	public function __construct(
		private BlockIdMetaUpgrader $blockIdMetaUpgrader,
		private BlockStateUpgrader $blockStateUpgrader
	) {
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeIntIdMeta(int $id, int $meta) : BlockStateData
	{
		return $this->blockIdMetaUpgrader->fromIntIdMeta($id, $meta);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeStringIdMeta(string $id, int $meta) : BlockStateData
	{
		return $this->blockIdMetaUpgrader->fromStringIdMeta($id, $meta);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function upgradeBlockStateNbt(CompoundTag $tag) : BlockStateData
	{
		if ($tag->getTag("name") !== null && $tag->getTag("val") !== null) {
			//Legacy (pre-1.13) blockstate - upgrade it to a version we understand
			$id = $tag->getString("name");
			$data = $tag->getShort("val");

			$blockStateData = $this->upgradeStringIdMeta($id, $data);
		} else {
			//Modern (post-1.13) blockstate
			$blockStateData = BlockStateData::fromNbt($tag);
		}

		return $this->blockStateUpgrader->upgrade($blockStateData);
	}

	public function getBlockStateUpgrader() : BlockStateUpgrader
	{
		return $this->blockStateUpgrader;
	}

	public function getBlockIdMetaUpgrader() : BlockIdMetaUpgrader
	{
		return $this->blockIdMetaUpgrader;
	}
}
