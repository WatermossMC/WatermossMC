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

use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;
use watermossmc\world\generator\hell\Nether;
use watermossmc\world\generator\normal\Normal;

use function array_keys;
use function strtolower;

final class GeneratorManager
{
	use SingletonTrait;

	/**
	 * @var GeneratorManagerEntry[] name => classname mapping
	 * @phpstan-var array<string, GeneratorManagerEntry>
	 */
	private array $list = [];

	public function __construct()
	{
		$this->addGenerator(Flat::class, "flat", function (string $preset) : ?InvalidGeneratorOptionsException {
			if ($preset === "") {
				return null;
			}
			try {
				FlatGeneratorOptions::parsePreset($preset);
				return null;
			} catch (InvalidGeneratorOptionsException $e) {
				return $e;
			}
		});
		$this->addGenerator(Normal::class, "normal", fn () => null);
		$this->addAlias("normal", "default");
		$this->addGenerator(Nether::class, "nether", fn () => null);
		$this->addAlias("nether", "hell");
	}

	/**
	 * @param string   $class           Fully qualified name of class that extends \watermossmc\world\generator\Generator
	 * @param string   $name            Alias for this generator type that can be written in configs
	 * @param \Closure $presetValidator Callback to validate generator options for new worlds
	 * @param bool     $overwrite       Whether to force overwriting any existing registered generator with the same name
	 *
	 * @phpstan-param \Closure(string) : ?InvalidGeneratorOptionsException $presetValidator
	 *
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addGenerator(string $class, string $name, \Closure $presetValidator, bool $overwrite = false) : void
	{
		Utils::testValidInstance($class, Generator::class);

		$name = strtolower($name);
		if (!$overwrite && isset($this->list[$name])) {
			throw new \InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		$this->list[$name] = new GeneratorManagerEntry($class, $presetValidator);
	}

	/**
	 * Aliases an already-registered generator name to another name. Useful if you want to map a generator name to an
	 * existing generator without having to replicate the parameters.
	 */
	public function addAlias(string $name, string $alias) : void
	{
		$name = strtolower($name);
		$alias = strtolower($alias);
		if (!isset($this->list[$name])) {
			throw new \InvalidArgumentException("Alias \"$name\" is not assigned");
		}
		if (isset($this->list[$alias])) {
			throw new \InvalidArgumentException("Alias \"$alias\" is already assigned");
		}
		$this->list[$alias] = $this->list[$name];
	}

	/**
	 * Returns a list of names for registered generators.
	 *
	 * @return string[]
	 */
	public function getGeneratorList() : array
	{
		return array_keys($this->list);
	}

	/**
	 * Returns the generator entry of a registered Generator matching the given name, or null if not found.
	 */
	public function getGenerator(string $name) : ?GeneratorManagerEntry
	{
		return $this->list[strtolower($name)] ?? null;
	}

	/**
	 * Returns the registered name of the given Generator class.
	 *
	 * @param string $class Fully qualified name of class that extends \watermossmc\world\generator\Generator
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws \InvalidArgumentException if the class type cannot be matched to a known alias
	 */
	public function getGeneratorName(string $class) : string
	{
		Utils::testValidInstance($class, Generator::class);
		foreach (Utils::stringifyKeys($this->list) as $name => $c) {
			if ($c->getGeneratorClass() === $class) {
				return $name;
			}
		}

		throw new \InvalidArgumentException("Generator class $class is not registered");
	}
}
