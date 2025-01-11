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

use function array_key_exists;
use function spl_object_id;

/**
 * @phpstan-template TObject of object
 */
trait IntSaveIdMapTrait
{
	/**
	 * @var object[]
	 * @phpstan-var array<int, TObject>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	/**
	 * @phpstan-param TObject $enum
	 */
	protected function getRuntimeId(object $enum) : int
	{
		//this is fine for enums and non-cloning object registries
		return spl_object_id($enum);
	}

	/**
	 * @phpstan-param TObject $enum
	 */
	public function register(int $saveId, object $enum) : void
	{
		$this->idToEnum[$saveId] = $enum;
		$this->enumToId[$this->getRuntimeId($enum)] = $saveId;
	}

	/**
	 * @phpstan-return TObject|null
	 */
	public function fromId(int $id) : ?object
	{
		//we might not have all the effect IDs registered
		return $this->idToEnum[$id] ?? null;
	}

	/**
	 * @phpstan-param TObject $enum
	 */
	public function toId(object $enum) : int
	{
		$runtimeId = $this->getRuntimeId($enum);
		if (!array_key_exists($runtimeId, $this->enumToId)) {
			//this should never happen, so we treat it as an exceptional condition
			throw new \InvalidArgumentException("Object does not have a mapped save ID");
		}
		return $this->enumToId[$runtimeId];
	}
}
