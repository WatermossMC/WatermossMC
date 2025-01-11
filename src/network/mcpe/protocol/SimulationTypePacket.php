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

class SimulationTypePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SIMULATION_TYPE_PACKET;

	public const GAME = 0;
	public const EDITOR = 1;
	public const TEST = 2;

	private int $type;

	/**
	 * @generate-create-func
	 */
	public static function create(int $type) : self
	{
		$result = new self();
		$result->type = $type;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->type);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSimulationType($this);
	}
}
