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
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\network\mcpe\protocol\types\StructureEditorData;

class StructureBlockUpdatePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_BLOCK_UPDATE_PACKET;

	public BlockPosition $blockPosition;
	public StructureEditorData $structureEditorData;
	public bool $isPowered;
	public bool $waterlogged;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, StructureEditorData $structureEditorData, bool $isPowered, bool $waterlogged) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->structureEditorData = $structureEditorData;
		$result->isPowered = $isPowered;
		$result->waterlogged = $waterlogged;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getBlockPosition();
		$this->structureEditorData = $in->getStructureEditorData();
		$this->isPowered = $in->getBool();
		$this->waterlogged = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->blockPosition);
		$out->putStructureEditorData($this->structureEditorData);
		$out->putBool($this->isPowered);
		$out->putBool($this->waterlogged);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleStructureBlockUpdate($this);
	}
}
