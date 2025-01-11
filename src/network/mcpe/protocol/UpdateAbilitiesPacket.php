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
use watermossmc\network\mcpe\protocol\types\AbilitiesData;

/**
 * Updates player abilities and permissions, such as command permissions, flying/noclip, fly speed, walk speed etc.
 * Abilities may be layered in order to combine different ability sets into a resulting set.
 */
class UpdateAbilitiesPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_ABILITIES_PACKET;

	private AbilitiesData $data;

	/**
	 * @generate-create-func
	 */
	public static function create(AbilitiesData $data) : self
	{
		$result = new self();
		$result->data = $data;
		return $result;
	}

	public function getData() : AbilitiesData
	{
		return $this->data;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->data = AbilitiesData::decode($in);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->data->encode($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateAbilities($this);
	}
}
