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

namespace watermossmc\world\format\io;

use Symfony\Component\Filesystem\Path;
use watermossmc\utils\Filesystem;
use watermossmc\world\format\Chunk;
use watermossmc\world\generator\GeneratorManager;
use watermossmc\world\generator\normal\Normal;
use watermossmc\world\WorldCreationOptions;

use function basename;
use function crc32;
use function file_exists;
use function floor;
use function microtime;
use function mkdir;
use function random_bytes;
use function rename;
use function round;
use function rtrim;

use const DIRECTORY_SEPARATOR;

class FormatConverter
{
	private string $backupPath;
	private \Logger $logger;

	public function __construct(
		private WorldProvider $oldProvider,
		private WritableWorldProviderManagerEntry $newProvider,
		string $backupPath,
		\Logger $logger,
		private int $chunksPerProgressUpdate = 256
	) {
		$this->logger = new \PrefixedLogger($logger, "World Converter: " . $this->oldProvider->getWorldData()->getName());

		if (!file_exists($backupPath)) {
			@mkdir($backupPath, 0777, true);
		}
		$nextSuffix = "";
		do {
			$this->backupPath = Path::join($backupPath, basename($this->oldProvider->getPath()) . $nextSuffix);
			$nextSuffix = "_" . crc32(random_bytes(4));
		} while (file_exists($this->backupPath));
	}

	public function getBackupPath() : string
	{
		return $this->backupPath;
	}

	public function execute() : void
	{
		$new = $this->generateNew();

		$this->populateLevelData($new->getWorldData());
		$this->convertTerrain($new);

		$path = $this->oldProvider->getPath();
		$this->oldProvider->close();
		$new->close();

		$this->logger->info("Backing up pre-conversion world to " . $this->backupPath);
		if (!@rename($path, $this->backupPath)) {
			$this->logger->warning("Moving old world files for backup failed, attempting copy instead. This might take a long time.");
			Filesystem::recursiveCopy($path, $this->backupPath);
			Filesystem::recursiveUnlink($path);
		}
		if (!@rename($new->getPath(), $path)) {
			//we don't expect this to happen because worlds/ should most likely be all on the same FS, but just in case...
			$this->logger->debug("Relocation of new world files to location failed, attempting copy and delete instead");
			Filesystem::recursiveCopy($new->getPath(), $path);
			Filesystem::recursiveUnlink($new->getPath());
		}

		$this->logger->info("Conversion completed");
	}

	private function generateNew() : WritableWorldProvider
	{
		$this->logger->info("Generating new world");
		$data = $this->oldProvider->getWorldData();

		$convertedOutput = rtrim($this->oldProvider->getPath(), "/" . DIRECTORY_SEPARATOR) . "_converted" . DIRECTORY_SEPARATOR;
		if (file_exists($convertedOutput)) {
			$this->logger->info("Found previous conversion attempt, deleting...");
			Filesystem::recursiveUnlink($convertedOutput);
		}
		$this->newProvider->generate(
			$convertedOutput,
			$data->getName(),
			WorldCreationOptions::create()
			//TODO: defaulting to NORMAL here really isn't very good behaviour, but it's consistent with what we already
			//did previously; besides, WorldManager checks for unknown generators before this is reached anyway.
			->setGeneratorClass(GeneratorManager::getInstance()->getGenerator($data->getGenerator())?->getGeneratorClass() ?? Normal::class)
			->setGeneratorOptions($data->getGeneratorOptions())
			->setSeed($data->getSeed())
			->setSpawnPosition($data->getSpawn())
			->setDifficulty($data->getDifficulty())
		);

		return $this->newProvider->fromPath($convertedOutput, $this->logger);
	}

	private function populateLevelData(WorldData $data) : void
	{
		$this->logger->info("Converting world manifest");
		$oldData = $this->oldProvider->getWorldData();
		$data->setDifficulty($oldData->getDifficulty());
		$data->setLightningLevel($oldData->getLightningLevel());
		$data->setLightningTime($oldData->getLightningTime());
		$data->setRainLevel($oldData->getRainLevel());
		$data->setRainTime($oldData->getRainTime());
		$data->setSpawn($oldData->getSpawn());
		$data->setTime($oldData->getTime());

		$data->save();
		$this->logger->info("Finished converting manifest");
		//TODO: add more properties as-needed
	}

	private function convertTerrain(WritableWorldProvider $new) : void
	{
		$this->logger->info("Calculating chunk count");
		$count = $this->oldProvider->calculateChunkCount();
		$this->logger->info("Discovered $count chunks");

		$counter = 0;

		$start = microtime(true);
		$thisRound = $start;
		foreach ($this->oldProvider->getAllChunks(true, $this->logger) as $coords => $loadedChunkData) {
			[$chunkX, $chunkZ] = $coords;
			$new->saveChunk($chunkX, $chunkZ, $loadedChunkData->getData(), Chunk::DIRTY_FLAGS_ALL);
			$counter++;
			if (($counter % $this->chunksPerProgressUpdate) === 0) {
				$time = microtime(true);
				$diff = $time - $thisRound;
				$thisRound = $time;
				$this->logger->info("Converted $counter / $count chunks (" . floor($this->chunksPerProgressUpdate / $diff) . " chunks/sec)");
			}
		}
		$total = microtime(true) - $start;
		$this->logger->info("Converted $counter / $counter chunks in " . round($total, 3) . " seconds (" . floor($counter / $total) . " chunks/sec)");
	}
}
