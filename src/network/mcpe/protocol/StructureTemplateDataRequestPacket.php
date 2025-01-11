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
use watermossmc\network\mcpe\protocol\types\StructureSettings;

class StructureTemplateDataRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_REQUEST_PACKET;

	public const TYPE_EXPORT_FROM_SAVE_MODE = 1;
	public const TYPE_EXPORT_FROM_LOAD_MODE = 2;
	public const TYPE_QUERY_SAVED_STRUCTURE = 3;

	public string $structureTemplateName;
	public BlockPosition $structureBlockPosition;
	public StructureSettings $structureSettings;
	public int $requestType;

	/**
	 * @generate-create-func
	 */
	public static function create(string $structureTemplateName, BlockPosition $structureBlockPosition, StructureSettings $structureSettings, int $requestType) : self
	{
		$result = new self();
		$result->structureTemplateName = $structureTemplateName;
		$result->structureBlockPosition = $structureBlockPosition;
		$result->structureSettings = $structureSettings;
		$result->requestType = $requestType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->structureTemplateName = $in->getString();
		$this->structureBlockPosition = $in->getBlockPosition();
		$this->structureSettings = $in->getStructureSettings();
		$this->requestType = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->structureTemplateName);
		$out->putBlockPosition($this->structureBlockPosition);
		$out->putStructureSettings($this->structureSettings);
		$out->putByte($this->requestType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleStructureTemplateDataRequest($this);
	}
}
