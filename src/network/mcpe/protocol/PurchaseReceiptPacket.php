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

use function count;

class PurchaseReceiptPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PURCHASE_RECEIPT_PACKET;

	/** @var string[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param string[] $entries
	 */
	public static function create(array $entries) : self
	{
		$result = new self();
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$count = $in->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$this->entries[] = $in->getString();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$out->putString($entry);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePurchaseReceipt($this);
	}
}
