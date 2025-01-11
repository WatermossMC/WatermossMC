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

namespace watermossmc\data\runtime;

use function array_values;
use function ceil;
use function count;
use function log;
use function spl_object_id;
use function usort;

/**
 * A big hack to allow lazily associating enum cases with packed bit values for RuntimeDataDescriber :)
 *
 * @internal
 * @phpstan-template T of \UnitEnum
 */
final class RuntimeEnumMetadata
{
	public readonly int $bits;

	/**
	 * @var object[]
	 * @phpstan-var list<T>
	 */
	private readonly array $intToEnum;
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private readonly array $enumToInt;

	/**
	 * @param \UnitEnum[] $members
	 * @phpstan-param list<T> $members
	 */
	public function __construct(
		array $members
	) {
		usort($members, fn (\UnitEnum $a, \UnitEnum $b) => $a->name <=> $b->name); //sort by name to ensure consistent ordering (and thus consistent bit assignments)

		$this->bits = (int) ceil(log(count($members), 2));
		$this->intToEnum = array_values($members);

		$reversed = [];
		foreach ($this->intToEnum as $int => $enum) {
			$reversed[spl_object_id($enum)] = $int;
		}

		$this->enumToInt = $reversed;
	}

	/**
	 * @phpstan-return T|null
	 */
	public function intToEnum(int $value) : ?object
	{
		return $this->intToEnum[$value] ?? null;
	}

	/**
	 * @phpstan-param T $enum
	 */
	public function enumToInt(object $enum) : int
	{
		return $this->enumToInt[spl_object_id($enum)];
	}

	/**
	 * @var self[]
	 * @phpstan-var array<class-string, object>
	 */
	private static array $cache = [];

	/**
	 * @phpstan-template TEnum of \UnitEnum
	 * @phpstan-param TEnum $case
	 *
	 * @phpstan-return self<TEnum>
	 */
	public static function from(\UnitEnum $case) : self
	{
		$class = $case::class;
		/** @phpstan-var self<TEnum>|null $metadata */
		$metadata = self::$cache[$class] ?? null;
		if ($metadata === null) {
			/**
			 * PHPStan can't infer this correctly :( https://github.com/phpstan/phpstan/issues/7162
			 * @phpstan-var list<TEnum> $cases
			 */
			$cases = $case::cases();
			self::$cache[$class] = $metadata = new self($cases);
		}

		return $metadata;
	}
}
