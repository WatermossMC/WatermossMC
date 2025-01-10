<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\cache;

use watermossmc\data\bedrock\BedrockDataFiles;
use watermossmc\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use watermossmc\network\mcpe\protocol\BiomeDefinitionListPacket;
use watermossmc\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;
use watermossmc\utils\Filesystem;
use watermossmc\utils\SingletonTrait;

class StaticPacketCache
{
	use SingletonTrait;

	/**
	 * @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag>
	 */
	private static function loadCompoundFromFile(string $filePath) : CacheableNbt
	{
		return new CacheableNbt((new NetworkNbtSerializer())->read(Filesystem::fileGetContents($filePath))->mustGetCompoundTag());
	}

	private static function make() : self
	{
		return new self(
			BiomeDefinitionListPacket::create(self::loadCompoundFromFile(BedrockDataFiles::BIOME_DEFINITIONS_NBT)),
			AvailableActorIdentifiersPacket::create(self::loadCompoundFromFile(BedrockDataFiles::ENTITY_IDENTIFIERS_NBT))
		);
	}

	public function __construct(
		private BiomeDefinitionListPacket $biomeDefs,
		private AvailableActorIdentifiersPacket $availableActorIdentifiers
	) {
	}

	public function getBiomeDefs() : BiomeDefinitionListPacket
	{
		return $this->biomeDefs;
	}

	public function getAvailableActorIdentifiers() : AvailableActorIdentifiersPacket
	{
		return $this->availableActorIdentifiers;
	}
}
