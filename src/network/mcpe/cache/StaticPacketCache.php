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
