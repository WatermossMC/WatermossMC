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

namespace watermossmc\network\mcpe\convert;

use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\data\bedrock\block\BlockStateSerializeException;
use watermossmc\data\bedrock\block\BlockStateSerializer;
use watermossmc\data\bedrock\block\BlockTypeNames;
use watermossmc\utils\AssumptionFailedError;

/**
 * @internal
 */
final class BlockTranslator
{
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $networkIdCache = [];

	/** Used when a blockstate can't be correctly serialized (e.g. because it's unknown) */
	private BlockStateData $fallbackStateData;
	private int $fallbackStateId;

	public function __construct(
		private BlockStateDictionary $blockStateDictionary,
		private BlockStateSerializer $blockStateSerializer
	) {
		$this->fallbackStateData = BlockStateData::current(BlockTypeNames::INFO_UPDATE, []);
		$this->fallbackStateId = $this->blockStateDictionary->lookupStateIdFromData($this->fallbackStateData) ??
			throw new AssumptionFailedError(BlockTypeNames::INFO_UPDATE . " should always exist");
	}

	public function internalIdToNetworkId(int $internalStateId) : int
	{
		if (isset($this->networkIdCache[$internalStateId])) {
			return $this->networkIdCache[$internalStateId];
		}

		try {
			$blockStateData = $this->blockStateSerializer->serialize($internalStateId);

			$networkId = $this->blockStateDictionary->lookupStateIdFromData($blockStateData);
			if ($networkId === null) {
				throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
			}
		} catch (BlockStateSerializeException) {
			//TODO: this will swallow any error caused by invalid block properties; this is not ideal, but it should be
			//covered by unit tests, so this is probably a safe assumption.
			$networkId = $this->fallbackStateId;
		}

		return $this->networkIdCache[$internalStateId] = $networkId;
	}

	/**
	 * Looks up the network state data associated with the given internal state ID.
	 */
	public function internalIdToNetworkStateData(int $internalStateId) : BlockStateData
	{
		//we don't directly use the blockstate serializer here - we can't assume that the network blockstate NBT is the
		//same as the disk blockstate NBT, in case we decide to have different world version than network version (or in
		//case someone wants to implement multi version).
		$networkRuntimeId = $this->internalIdToNetworkId($internalStateId);

		return $this->blockStateDictionary->generateDataFromStateId($networkRuntimeId) ?? throw new AssumptionFailedError("We just looked up this state ID, so it must exist");
	}

	public function getBlockStateDictionary() : BlockStateDictionary
	{
		return $this->blockStateDictionary;
	}

	public function getFallbackStateData() : BlockStateData
	{
		return $this->fallbackStateData;
	}
}
