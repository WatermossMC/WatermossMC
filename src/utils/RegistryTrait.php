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

use function array_map;
use function count;
use function mb_strtoupper;
use function preg_match;

/**
 * This trait allows a class to simulate object class constants, since PHP doesn't currently support this.
 * These faux constants are exposed in static class methods, which are handled using __callStatic().
 *
 * Classes using this trait need to include \@method tags in their class docblock for every faux constant.
 * Alternatively, just put \@generate-registry-docblock in the docblock and run build/generate-registry-annotations.php
 */
trait RegistryTrait
{
	/**
	 * @var object[]|null
	 * @phpstan-var array<string, object>|null
	 */
	private static $members = null;

	private static function verifyName(string $name) : void
	{
		if (preg_match('/^(?!\d)[A-Za-z\d_]+$/u', $name) === 0) {
			throw new \InvalidArgumentException("Invalid member name \"$name\", should only contain letters, numbers and underscores, and must not start with a number");
		}
	}

	/**
	 * Adds the given object to the registry.
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function _registryRegister(string $name, object $member) : void
	{
		if (self::$members === null) {
			throw new AssumptionFailedError("Cannot register members outside of " . self::class . "::setup()");
		}
		self::verifyName($name);
		$upperName = mb_strtoupper($name);
		if (isset(self::$members[$upperName])) {
			throw new \InvalidArgumentException("\"$upperName\" is already reserved");
		}
		self::$members[$upperName] = $member;
	}

	/**
	 * Inserts default entries into the registry.
	 *
	 * (This ought to be private, but traits suck too much for that.)
	 */
	abstract protected static function setup() : void;

	/**
	 * @internal Lazy-inits the enum if necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function checkInit() : void
	{
		if (self::$members === null) {
			self::$members = [];
			self::setup();
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function _registryFromString(string $name) : object
	{
		self::checkInit();
		if (self::$members === null) {
			throw new AssumptionFailedError(self::class . "::checkInit() did not initialize self::\$members correctly");
		}
		$upperName = mb_strtoupper($name);
		if (!isset(self::$members[$upperName])) {
			throw new \InvalidArgumentException("No such registry member: " . self::class . "::" . $upperName);
		}
		return self::preprocessMember(self::$members[$upperName]);
	}

	protected static function preprocessMember(object $member) : object
	{
		return $member;
	}

	/**
	 * @param string  $name
	 * @param mixed[] $arguments
	 * @phpstan-param list<mixed> $arguments
	 *
	 * @return object
	 */
	public static function __callStatic($name, $arguments)
	{
		if (count($arguments) > 0) {
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}

		//fast path
		if (self::$members !== null && isset(self::$members[$name])) {
			return self::preprocessMember(self::$members[$name]);
		}

		//fallback
		try {
			return self::_registryFromString($name);
		} catch (\InvalidArgumentException $e) {
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @return object[]
	 * @phpstan-return array<string, object>
	 */
	private static function _registryGetAll() : array
	{
		self::checkInit();
		return array_map(self::preprocessMember(...), self::$members ?? throw new AssumptionFailedError(self::class . "::checkInit() did not initialize self::\$members correctly"));
	}
}
