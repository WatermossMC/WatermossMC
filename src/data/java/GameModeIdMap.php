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

namespace watermossmc\data\java;

use watermossmc\player\GameMode;
use watermossmc\utils\SingletonTrait;

use function array_key_exists;
use function spl_object_id;

final class GameModeIdMap
{
	use SingletonTrait;

	/**
	 * @var GameMode[]
	 * @phpstan-var array<int, GameMode>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	public function __construct()
	{
		foreach (GameMode::cases() as $case) {
			$this->register(match($case) {
				GameMode::SURVIVAL => 0,
				GameMode::CREATIVE => 1,
				GameMode::ADVENTURE => 2,
				GameMode::SPECTATOR => 3,
			}, $case);
		}
	}

	private function register(int $id, GameMode $type) : void
	{
		$this->idToEnum[$id] = $type;
		$this->enumToId[spl_object_id($type)] = $id;
	}

	public function fromId(int $id) : ?GameMode
	{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(GameMode $type) : int
	{
		$k = spl_object_id($type);
		if (!array_key_exists($k, $this->enumToId)) {
			throw new \InvalidArgumentException("Game mode $type->name does not have a mapped ID"); //this should never happen
		}
		return $this->enumToId[$k];
	}
}
