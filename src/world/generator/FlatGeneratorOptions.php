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

namespace watermossmc\world\generator;

use watermossmc\data\bedrock\BiomeIds;
use watermossmc\item\LegacyStringToItemParser;
use watermossmc\item\LegacyStringToItemParserException;

use function array_map;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;

/**
 * @internal
 */
final class FlatGeneratorOptions
{
	/**
	 * @param int[]   $structure
	 * @param mixed[] $extraOptions
	 * @phpstan-param array<int, int> $structure
	 * @phpstan-param array<string, array<string, string>|true> $extraOptions
	 */
	public function __construct(
		private array $structure,
		private int $biomeId,
		private array $extraOptions = []
	) {
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getStructure() : array
	{
		return $this->structure;
	}

	public function getBiomeId() : int
	{
		return $this->biomeId;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, array<string, string>|true>
	 */
	public function getExtraOptions() : array
	{
		return $this->extraOptions;
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public static function parseLayers(string $layers) : array
	{
		$result = [];
		$split = array_map('\trim', explode(',', $layers));
		$y = 0;
		$itemParser = LegacyStringToItemParser::getInstance();
		foreach ($split as $line) {
			preg_match('#^(?:(\d+)[x|*])?(.+)$#', $line, $matches);
			if (count($matches) !== 3) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\"");
			}

			$cnt = $matches[1] !== "" ? (int) $matches[1] : 1;
			try {
				$b = $itemParser->parse($matches[2])->getBlock();
			} catch (LegacyStringToItemParserException $e) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\": " . $e->getMessage(), 0, $e);
			}
			for ($cY = $y, $y += $cnt; $cY < $y; ++$cY) {
				$result[$cY] = $b->getStateId();
			}
		}

		return $result;
	}

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public static function parsePreset(string $presetString) : self
	{
		$preset = explode(";", $presetString);
		$blocks = $preset[1] ?? "";
		$biomeId = (int) ($preset[2] ?? BiomeIds::PLAINS);
		$optionsString = $preset[3] ?? "";
		$structure = self::parseLayers($blocks);

		$options = [];
		//TODO: more error checking
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $optionsString, $matches);
		foreach ($matches[2] as $i => $option) {
			$params = true;
			if ($matches[3][$i] !== "") {
				$params = [];
				$p = explode(" ", $matches[3][$i]);
				foreach ($p as $k) {
					$k = explode("=", $k);
					if (isset($k[1])) {
						$params[$k[0]] = $k[1];
					}
				}
			}
			$options[(string) $option] = $params;
		}
		return new self($structure, $biomeId, $options);
	}

}
