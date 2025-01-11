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

namespace watermossmc\data\bedrock;

use watermossmc\utils\AssumptionFailedError;
use watermossmc\utils\Filesystem;
use watermossmc\utils\Utils;

use function is_array;
use function is_int;
use function is_string;
use function json_decode;

abstract class LegacyToStringIdMap
{
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $legacyToString = [];

	public function __construct(string $file)
	{
		$stringToLegacyId = json_decode(Filesystem::fileGetContents($file), true);
		if (!is_array($stringToLegacyId)) {
			throw new AssumptionFailedError("Invalid format of ID map");
		}
		foreach (Utils::promoteKeys($stringToLegacyId) as $stringId => $legacyId) {
			if (!is_string($stringId) || !is_int($legacyId)) {
				throw new AssumptionFailedError("ID map should have string keys and int values");
			}
			$this->legacyToString[$legacyId] = $stringId;
		}
	}

	public function legacyToString(int $legacy) : ?string
	{
		return $this->legacyToString[$legacy] ?? null;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<int, string>
	 */
	public function getLegacyToStringMap() : array
	{
		return $this->legacyToString;
	}

	public function add(string $string, int $legacy) : void
	{
		if (isset($this->legacyToString[$legacy])) {
			if ($this->legacyToString[$legacy] === $string) {
				return;
			}
			throw new \InvalidArgumentException("Legacy ID $legacy is already mapped to string " . $this->legacyToString[$legacy]);
		}
		$this->legacyToString[$legacy] = $string;
	}
}
