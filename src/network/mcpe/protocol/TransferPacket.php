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

class TransferPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::TRANSFER_PACKET;

	public string $address;
	public int $port = 19132;
	public bool $reloadWorld;

	/**
	 * @generate-create-func
	 */
	public static function create(string $address, int $port, bool $reloadWorld) : self
	{
		$result = new self();
		$result->address = $address;
		$result->port = $port;
		$result->reloadWorld = $reloadWorld;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getString();
		$this->port = $in->getLShort();
		$this->reloadWorld = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->address);
		$out->putLShort($this->port);
		$out->putBool($this->reloadWorld);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleTransfer($this);
	}
}
