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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\InventoryTransactionPacket;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class ReleaseItemTransactionData extends TransactionData
{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_RELEASE_ITEM;

	public const ACTION_RELEASE = 0; //bow shoot
	public const ACTION_CONSUME = 1; //eat food, drink potion

	private int $actionType;
	private int $hotbarSlot;
	private ItemStackWrapper $itemInHand;
	private Vector3 $headPosition;

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper
	{
		return $this->itemInHand;
	}

	public function getHeadPosition() : Vector3
	{
		return $this->headPosition;
	}

	protected function decodeData(PacketSerializer $stream) : void
	{
		$this->actionType = $stream->getUnsignedVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = $stream->getItemStackWrapper();
		$this->headPosition = $stream->getVector3();
	}

	protected function encodeData(PacketSerializer $stream) : void
	{
		$stream->putUnsignedVarInt($this->actionType);
		$stream->putVarInt($this->hotbarSlot);
		$stream->putItemStackWrapper($this->itemInHand);
		$stream->putVector3($this->headPosition);
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $headPosition) : self
	{
		$result = new self();
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->headPosition = $headPosition;

		return $result;
	}
}
