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

use function count;

class UpdateSoftEnumPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_SOFT_ENUM_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;
	public const TYPE_SET = 2;

	public string $enumName;
	/** @var string[] */
	public array $values = [];
	public int $type;

	/**
	 * @generate-create-func
	 * @param string[] $values
	 */
	public static function create(string $enumName, array $values, int $type) : self
	{
		$result = new self();
		$result->enumName = $enumName;
		$result->values = $values;
		$result->type = $type;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->enumName = $in->getString();
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->values[] = $in->getString();
		}
		$this->type = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->enumName);
		$out->putUnsignedVarInt(count($this->values));
		foreach ($this->values as $v) {
			$out->putString($v);
		}
		$out->putByte($this->type);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateSoftEnum($this);
	}
}
