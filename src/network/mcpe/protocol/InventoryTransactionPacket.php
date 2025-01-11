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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use watermossmc\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use watermossmc\network\mcpe\protocol\types\inventory\NormalTransactionData;
use watermossmc\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use watermossmc\network\mcpe\protocol\types\inventory\TransactionData;
use watermossmc\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use watermossmc\network\mcpe\protocol\types\inventory\UseItemTransactionData;

use function count;

/**
 * This packet effectively crams multiple packets into one.
 */
class InventoryTransactionPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_TRANSACTION_PACKET;

	public const TYPE_NORMAL = 0;
	public const TYPE_MISMATCH = 1;
	public const TYPE_USE_ITEM = 2;
	public const TYPE_USE_ITEM_ON_ENTITY = 3;
	public const TYPE_RELEASE_ITEM = 4;

	public int $requestId;
	/** @var InventoryTransactionChangedSlotsHack[] */
	public array $requestChangedSlots;
	public TransactionData $trData;

	/**
	 * @generate-create-func
	 * @param InventoryTransactionChangedSlotsHack[] $requestChangedSlots
	 */
	public static function create(int $requestId, array $requestChangedSlots, TransactionData $trData) : self
	{
		$result = new self();
		$result->requestId = $requestId;
		$result->requestChangedSlots = $requestChangedSlots;
		$result->trData = $trData;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->requestId = $in->readLegacyItemStackRequestId();
		$this->requestChangedSlots = [];
		if ($this->requestId !== 0) {
			for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
				$this->requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($in);
			}
		}

		$transactionType = $in->getUnsignedVarInt();

		$this->trData = match($transactionType) {
			NormalTransactionData::ID => new NormalTransactionData(),
			MismatchTransactionData::ID => new MismatchTransactionData(),
			UseItemTransactionData::ID => new UseItemTransactionData(),
			UseItemOnEntityTransactionData::ID => new UseItemOnEntityTransactionData(),
			ReleaseItemTransactionData::ID => new ReleaseItemTransactionData(),
			default => throw new PacketDecodeException("Unknown transaction type $transactionType"),
		};

		$this->trData->decode($in);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->writeLegacyItemStackRequestId($this->requestId);
		if ($this->requestId !== 0) {
			$out->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach ($this->requestChangedSlots as $changedSlots) {
				$changedSlots->write($out);
			}
		}

		$out->putUnsignedVarInt($this->trData->getTypeId());

		$this->trData->encode($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleInventoryTransaction($this);
	}
}
