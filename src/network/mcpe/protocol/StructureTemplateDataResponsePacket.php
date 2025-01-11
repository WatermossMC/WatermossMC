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
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

class StructureTemplateDataResponsePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_RESPONSE_PACKET;

	public const TYPE_FAILURE = 0;
	public const TYPE_EXPORT = 1;
	public const TYPE_QUERY = 2;

	public string $structureTemplateName;
	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public ?CacheableNbt $nbt;
	public int $responseType;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(string $structureTemplateName, ?CacheableNbt $nbt, int $responseType) : self
	{
		$result = new self();
		$result->structureTemplateName = $structureTemplateName;
		$result->nbt = $nbt;
		$result->responseType = $responseType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->structureTemplateName = $in->getString();
		if ($in->getBool()) {
			$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
		}
		$this->responseType = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->structureTemplateName);
		$out->putBool($this->nbt !== null);
		if ($this->nbt !== null) {
			$out->put($this->nbt->getEncodedNbt());
		}
		$out->putByte($this->responseType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleStructureTemplateDataResponse($this);
	}
}
