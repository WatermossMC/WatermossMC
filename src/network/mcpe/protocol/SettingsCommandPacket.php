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

class SettingsCommandPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SETTINGS_COMMAND_PACKET;

	private string $command;
	private bool $suppressOutput;

	/**
	 * @generate-create-func
	 */
	public static function create(string $command, bool $suppressOutput) : self
	{
		$result = new self();
		$result->command = $command;
		$result->suppressOutput = $suppressOutput;
		return $result;
	}

	public function getCommand() : string
	{
		return $this->command;
	}

	public function getSuppressOutput() : bool
	{
		return $this->suppressOutput;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->command = $in->getString();
		$this->suppressOutput = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->command);
		$out->putBool($this->suppressOutput);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSettingsCommand($this);
	}
}
