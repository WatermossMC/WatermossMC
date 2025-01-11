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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class SubChunkPacketEntryCommon
{
	public function __construct(
		private SubChunkPositionOffset $offset,
		private int $requestResult,
		private string $terrainData,
		private ?SubChunkPacketHeightMapInfo $heightMap
	) {
	}

	public function getOffset() : SubChunkPositionOffset
	{
		return $this->offset;
	}

	public function getRequestResult() : int
	{
		return $this->requestResult;
	}

	public function getTerrainData() : string
	{
		return $this->terrainData;
	}

	public function getHeightMap() : ?SubChunkPacketHeightMapInfo
	{
		return $this->heightMap;
	}

	public static function read(PacketSerializer $in, bool $cacheEnabled) : self
	{
		$offset = SubChunkPositionOffset::read($in);

		$requestResult = $in->getByte();

		$data = !$cacheEnabled || $requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR ? $in->getString() : "";

		$heightMapDataType = $in->getByte();
		$heightMapData = match ($heightMapDataType) {
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => SubChunkPacketHeightMapInfo::read($in),
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			default => throw new PacketDecodeException("Unknown heightmap data type $heightMapDataType")
		};

		return new self(
			$offset,
			$requestResult,
			$data,
			$heightMapData
		);
	}

	public function write(PacketSerializer $out, bool $cacheEnabled) : void
	{
		$this->offset->write($out);

		$out->putByte($this->requestResult);

		if (!$cacheEnabled || $this->requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR) {
			$out->putString($this->terrainData);
		}

		if ($this->heightMap === null) {
			$out->putByte(SubChunkPacketHeightMapType::NO_DATA);
		} elseif ($this->heightMap->isAllTooLow()) {
			$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_LOW);
		} elseif ($this->heightMap->isAllTooHigh()) {
			$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_HIGH);
		} else {
			$heightMapData = $this->heightMap; //avoid PHPStan purity issue
			$out->putByte(SubChunkPacketHeightMapType::DATA);
			$heightMapData->write($out);
		}
	}
}
