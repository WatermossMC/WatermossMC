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

namespace watermossmc\player;

use Symfony\Component\Filesystem\Path;
use watermossmc\errorhandler\ErrorToExceptionHandler;
use watermossmc\nbt\BigEndianNbtSerializer;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\TreeRoot;
use watermossmc\utils\Filesystem;
use watermossmc\utils\Utils;

use function file_exists;
use function rename;
use function strtolower;
use function zlib_decode;
use function zlib_encode;

use const ZLIB_ENCODING_GZIP;

/**
 * Stores player data in a single .dat file per player. Each file is gzipped big-endian NBT.
 */
final class DatFilePlayerDataProvider implements PlayerDataProvider
{
	public function __construct(
		private string $path
	) {
	}

	private function getPlayerDataPath(string $username) : string
	{
		return Path::join($this->path, strtolower($username) . '.dat');
	}

	private function handleCorruptedPlayerData(string $name) : void
	{
		$path = $this->getPlayerDataPath($name);
		rename($path, $path . '.bak');
	}

	public function hasData(string $name) : bool
	{
		return file_exists($this->getPlayerDataPath($name));
	}

	public function loadData(string $name) : ?CompoundTag
	{
		$name = strtolower($name);
		$path = $this->getPlayerDataPath($name);

		if (!file_exists($path)) {
			return null;
		}

		try {
			$contents = Filesystem::fileGetContents($path);
		} catch (\RuntimeException $e) {
			throw new PlayerDataLoadException("Failed to read player data file \"$path\": " . $e->getMessage(), 0, $e);
		}
		try {
			$decompressed = ErrorToExceptionHandler::trapAndRemoveFalse(fn () => zlib_decode($contents));
		} catch (\ErrorException $e) {
			$this->handleCorruptedPlayerData($name);
			throw new PlayerDataLoadException("Failed to decompress raw player data for \"$name\": " . $e->getMessage(), 0, $e);
		}

		try {
			return (new BigEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
		} catch (NbtDataException $e) { //corrupt data
			$this->handleCorruptedPlayerData($name);
			throw new PlayerDataLoadException("Failed to decode NBT data for \"$name\": " . $e->getMessage(), 0, $e);
		}
	}

	public function saveData(string $name, CompoundTag $data) : void
	{
		$nbt = new BigEndianNbtSerializer();
		$contents = Utils::assumeNotFalse(zlib_encode($nbt->write(new TreeRoot($data)), ZLIB_ENCODING_GZIP), "zlib_encode() failed unexpectedly");
		try {
			Filesystem::safeFilePutContents($this->getPlayerDataPath($name), $contents);
		} catch (\RuntimeException $e) {
			throw new PlayerDataSaveException("Failed to write player data file: " . $e->getMessage(), 0, $e);
		}
	}
}
