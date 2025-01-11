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

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use watermossmc\network\mcpe\protocol\types\inventory\UseItemTransactionData;

use function count;

final class ItemInteractionData
{
	/**
	 * @param InventoryTransactionChangedSlotsHack[] $requestChangedSlots
	 */
	public function __construct(
		private int $requestId,
		private array $requestChangedSlots,
		private UseItemTransactionData $transactionData
	) {
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/**
	 * @return InventoryTransactionChangedSlotsHack[]
	 */
	public function getRequestChangedSlots() : array
	{
		return $this->requestChangedSlots;
	}

	public function getTransactionData() : UseItemTransactionData
	{
		return $this->transactionData;
	}

	public static function read(PacketSerializer $in) : self
	{
		$requestId = $in->getVarInt();
		$requestChangedSlots = [];
		if ($requestId !== 0) {
			$len = $in->getUnsignedVarInt();
			for ($i = 0; $i < $len; ++$i) {
				$requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($in);
			}
		}
		$transactionData = new UseItemTransactionData();
		$transactionData->decode($in);
		return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->requestId);
		if ($this->requestId !== 0) {
			$out->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach ($this->requestChangedSlots as $changedSlot) {
				$changedSlot->write($out);
			}
		}
		$this->transactionData->encode($out);
	}
}
