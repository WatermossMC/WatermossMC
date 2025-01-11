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

class CurrentStructureFeaturePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CURRENT_STRUCTURE_FEATURE_PACKET;

	public string $currentStructureFeature;

	/**
	 * @generate-create-func
	 */
	public static function create(string $currentStructureFeature) : self
	{
		$result = new self();
		$result->currentStructureFeature = $currentStructureFeature;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->currentStructureFeature = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->currentStructureFeature);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCurrentStructureFeature($this);
	}
}
