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
use watermossmc\network\mcpe\protocol\types\DimensionData;
use watermossmc\network\mcpe\protocol\types\DimensionNameIds;

use function count;

/**
 * Sets properties of different dimensions of the world, such as its Y axis bounds and generator used
 */
class DimensionDataPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::DIMENSION_DATA_PACKET;

	/**
	 * @var DimensionData[]
	 * @phpstan-var array<DimensionNameIds::*, DimensionData>
	 */
	private array $definitions;

	/**
	 * @generate-create-func
	 * @param DimensionData[] $definitions
	 * @phpstan-param array<DimensionNameIds::*, DimensionData> $definitions
	 */
	public static function create(array $definitions) : self
	{
		$result = new self();
		$result->definitions = $definitions;
		return $result;
	}

	/**
	 * @return DimensionData[]
	 * @phpstan-return array<DimensionNameIds::*, DimensionData>
	 */
	public function getDefinitions() : array
	{
		return $this->definitions;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->definitions = [];

		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++) {
			$dimensionNameId = $in->getString();
			$dimensionData = DimensionData::read($in);

			if (isset($this->definitions[$dimensionNameId])) {
				throw new PacketDecodeException("Repeated dimension data for key \"$dimensionNameId\"");
			}
			if ($dimensionNameId !== DimensionNameIds::OVERWORLD && $dimensionNameId !== DimensionNameIds::NETHER && $dimensionNameId !== DimensionNameIds::THE_END) {
				throw new PacketDecodeException("Invalid dimension name ID \"$dimensionNameId\"");
			}
			$this->definitions[$dimensionNameId] = $dimensionData;
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->definitions));

		foreach ($this->definitions as $dimensionNameId => $definition) {
			$out->putString((string) $dimensionNameId); //@phpstan-ignore-line
			$definition->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleDimensionData($this);
	}
}
