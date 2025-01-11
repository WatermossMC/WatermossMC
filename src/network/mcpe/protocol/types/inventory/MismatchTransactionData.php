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

use watermossmc\network\mcpe\protocol\InventoryTransactionPacket;
use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

use function count;

class MismatchTransactionData extends TransactionData
{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_MISMATCH;

	protected function decodeData(PacketSerializer $stream) : void
	{
		if (count($this->actions) > 0) {
			throw new PacketDecodeException("Mismatch transaction type should not have any actions associated with it, but got " . count($this->actions));
		}
	}

	protected function encodeData(PacketSerializer $stream) : void
	{

	}

	public static function new() : self
	{
		return new self(); //no arguments
	}
}
