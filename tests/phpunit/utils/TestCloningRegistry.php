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

namespace watermossmc\utils;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
 *
 * @method static \stdClass TEST1()
 * @method static \stdClass TEST2()
 * @method static \stdClass TEST3()
 */
final class TestCloningRegistry
{
	use CloningRegistryTrait;

	/**
	 * @return \stdClass[]
	 * @phpstan-return array<string, \stdClass>
	 */
	public static function getAll() : array
	{
		/**
		 * @var \stdClass[] $result
		 * @phpstan-var array<string, \stdClass> $result
		 */
		$result = self::_registryGetAll();
		return $result;
	}

	public static function fromString(string $s) : \stdClass
	{
		/** @var \stdClass $result */
		$result = self::_registryFromString($s);
		return $result;
	}

	protected static function setup() : void
	{
		self::_registryRegister("test1", new \stdClass());
		self::_registryRegister("test2", new \stdClass());
		self::_registryRegister("test3", new \stdClass());
	}
}
