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

namespace watermossmc\world\format\io\region;

use Symfony\Component\Filesystem\Path;
use watermossmc\nbt\NBT;
use watermossmc\nbt\tag\ByteArrayTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\ListTag;
use watermossmc\world\format\io\BaseWorldProvider;
use watermossmc\world\format\io\data\JavaWorldData;
use watermossmc\world\format\io\exception\CorruptedChunkException;
use watermossmc\world\format\io\LoadedChunkData;
use watermossmc\world\format\io\WorldData;

use function assert;
use function file_exists;
use function is_dir;
use function is_int;
use function morton2d_encode;
use function rename;
use function scandir;
use function strlen;
use function strrpos;
use function substr;
use function time;

use const SCANDIR_SORT_NONE;

abstract class RegionWorldProvider extends BaseWorldProvider
{
	/**
	 * Returns the file extension used for regions in this region-based format.
	 */
	abstract protected static function getRegionFileExtension() : string;

	/**
	 * Returns the storage version as per Minecraft PC world formats.
	 */
	abstract protected static function getPcWorldFormatVersion() : int;

	public static function isValid(string $path) : bool
	{
		if (file_exists(Path::join($path, "level.dat")) && is_dir($regionPath = Path::join($path, "region"))) {
			foreach (scandir($regionPath, SCANDIR_SORT_NONE) as $file) {
				$extPos = strrpos($file, ".");
				if ($extPos !== false && substr($file, $extPos + 1) === static::getRegionFileExtension()) {
					//we don't care if other region types exist, we only care if this format is possible
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @var RegionLoader[]
	 * @phpstan-var array<int, RegionLoader>
	 */
	protected array $regions = [];

	protected function loadLevelData() : WorldData
	{
		return new JavaWorldData(Path::join($this->getPath(), "level.dat"));
	}

	public function doGarbageCollection() : void
	{
		$limit = time() - 300;
		foreach ($this->regions as $index => $region) {
			if ($region->lastUsed <= $limit) {
				$region->close();
				unset($this->regions[$index]);
			}
		}
	}

	/**
	 * @param int $regionX reference parameter
	 * @param int $regionZ reference parameter
	 * @phpstan-param-out int $regionX
	 * @phpstan-param-out int $regionZ
	 */
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$regionX, &$regionZ) : void
	{
		$regionX = $chunkX >> 5;
		$regionZ = $chunkZ >> 5;
	}

	protected function getRegion(int $regionX, int $regionZ) : ?RegionLoader
	{
		return $this->regions[morton2d_encode($regionX, $regionZ)] ?? null;
	}

	/**
	 * Returns the path to a specific region file based on its X/Z coordinates
	 */
	protected function pathToRegion(int $regionX, int $regionZ) : string
	{
		return Path::join($this->path, "region", "r.$regionX.$regionZ." . static::getRegionFileExtension());
	}

	protected function loadRegion(int $regionX, int $regionZ) : RegionLoader
	{
		if (!isset($this->regions[$index = morton2d_encode($regionX, $regionZ)])) {
			$path = $this->pathToRegion($regionX, $regionZ);

			try {
				$this->regions[$index] = RegionLoader::loadExisting($path);
			} catch (CorruptedRegionException $e) {
				$this->logger->error("Corrupted region file detected: " . $e->getMessage());

				$backupPath = $path . ".bak." . time();
				rename($path, $backupPath);
				$this->logger->error("Corrupted region file has been backed up to " . $backupPath);

				$this->regions[$index] = RegionLoader::createNew($path);
			}
		}
		return $this->regions[$index];
	}

	protected function unloadRegion(int $regionX, int $regionZ) : void
	{
		if (isset($this->regions[$hash = morton2d_encode($regionX, $regionZ)])) {
			$this->regions[$hash]->close();
			unset($this->regions[$hash]);
		}
	}

	public function close() : void
	{
		foreach ($this->regions as $index => $region) {
			$region->close();
			unset($this->regions[$index]);
		}
	}

	/**
	 * @throws CorruptedChunkException
	 */
	abstract protected function deserializeChunk(string $data, \Logger $logger) : ?LoadedChunkData;

	/**
	 * @return CompoundTag[]
	 * @throws CorruptedChunkException
	 */
	protected static function getCompoundList(string $context, ListTag $list) : array
	{
		if ($list->count() === 0) { //empty lists might have wrong types, we don't care
			return [];
		}
		if ($list->getTagType() !== NBT::TAG_Compound) {
			throw new CorruptedChunkException("Expected TAG_List<TAG_Compound> for '$context'");
		}
		$result = [];
		foreach ($list as $tag) {
			if (!($tag instanceof CompoundTag)) {
				//this should never happen, but it's still possible due to lack of native type safety
				throw new CorruptedChunkException("Expected TAG_List<TAG_Compound> for '$context'");
			}
			$result[] = $tag;
		}
		return $result;
	}

	protected static function readFixedSizeByteArray(CompoundTag $chunk, string $tagName, int $length) : string
	{
		$tag = $chunk->getTag($tagName);
		if (!($tag instanceof ByteArrayTag)) {
			if ($tag === null) {
				throw new CorruptedChunkException("'$tagName' key is missing from chunk NBT");
			}
			throw new CorruptedChunkException("Expected TAG_ByteArray for '$tagName'");
		}
		$data = $tag->getValue();
		if (strlen($data) !== $length) {
			throw new CorruptedChunkException("Expected '$tagName' payload to have exactly $length bytes, but have " . strlen($data));
		}
		return $data;
	}

	/**
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?LoadedChunkData
	{
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		assert(is_int($regionX) && is_int($regionZ));

		if (!file_exists($this->pathToRegion($regionX, $regionZ))) {
			return null;
		}

		$chunkData = $this->loadRegion($regionX, $regionZ)->readChunk($chunkX & 0x1f, $chunkZ & 0x1f);
		if ($chunkData !== null) {
			return $this->deserializeChunk($chunkData, new \PrefixedLogger($this->logger, "Loading chunk x=$chunkX z=$chunkZ"));
		}

		return null;
	}

	private function createRegionIterator() : \RegexIterator
	{
		return new \RegexIterator(
			new \FilesystemIterator(
				Path::join($this->path, 'region'),
				\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
			),
			'/\/r\.(-?\d+)\.(-?\d+)\.' . static::getRegionFileExtension() . '$/',
			\RegexIterator::GET_MATCH
		);
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator
	{
		$iterator = $this->createRegionIterator();

		foreach ($iterator as $region) {
			$regionX = ((int) $region[1]);
			$regionZ = ((int) $region[2]);
			$rX = $regionX << 5;
			$rZ = $regionZ << 5;

			for ($chunkX = $rX; $chunkX < $rX + 32; ++$chunkX) {
				for ($chunkZ = $rZ; $chunkZ < $rZ + 32; ++$chunkZ) {
					try {
						$chunk = $this->loadChunk($chunkX, $chunkZ);
						if ($chunk !== null) {
							yield [$chunkX, $chunkZ] => $chunk;
						}
					} catch (CorruptedChunkException $e) {
						if (!$skipCorrupted) {
							throw $e;
						}
						if ($logger !== null) {
							$logger->error("Skipped corrupted chunk $chunkX $chunkZ (" . $e->getMessage() . ")");
						}
					}
				}
			}

			$this->unloadRegion($regionX, $regionZ);
		}
	}

	public function calculateChunkCount() : int
	{
		$count = 0;
		foreach ($this->createRegionIterator() as $region) {
			$regionX = ((int) $region[1]);
			$regionZ = ((int) $region[2]);
			$count += $this->loadRegion($regionX, $regionZ)->calculateChunkCount();
			$this->unloadRegion($regionX, $regionZ);
		}
		return $count;
	}
}
