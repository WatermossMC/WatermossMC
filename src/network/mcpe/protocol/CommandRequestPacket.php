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
use watermossmc\network\mcpe\protocol\types\command\CommandOriginData;

class CommandRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::COMMAND_REQUEST_PACKET;

	public string $command;
	public CommandOriginData $originData;
	public bool $isInternal;
	public int $version;

	/**
	 * @generate-create-func
	 */
	public static function create(string $command, CommandOriginData $originData, bool $isInternal, int $version) : self
	{
		$result = new self();
		$result->command = $command;
		$result->originData = $originData;
		$result->isInternal = $isInternal;
		$result->version = $version;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->command = $in->getString();
		$this->originData = $in->getCommandOriginData();
		$this->isInternal = $in->getBool();
		$this->version = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->command);
		$out->putCommandOriginData($this->originData);
		$out->putBool($this->isInternal);
		$out->putVarInt($this->version);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCommandRequest($this);
	}
}
