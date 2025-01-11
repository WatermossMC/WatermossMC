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

namespace watermossmc\command;

use watermossmc\lang\Translatable;
use watermossmc\utils\Utils;

/**
 * @phpstan-type Execute \Closure(CommandSender $sender, Command $command, string $commandLabel, list<string> $args) : mixed
 */
final class ClosureCommand extends Command
{
	/** @phpstan-var Execute */
	private \Closure $execute;

	/**
	 * @param string[] $permissions
	 * @phpstan-param Execute $execute
	 */
	public function __construct(
		string $name,
		\Closure $execute,
		array $permissions,
		Translatable|string $description = "",
		Translatable|string|null $usageMessage = null,
		array $aliases = []
	) {
		Utils::validateCallableSignature(
			fn (CommandSender $sender, Command $command, string $commandLabel, array $args) : mixed => 1,
			$execute,
		);
		$this->execute = $execute;
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->setPermissions($permissions);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		return ($this->execute)($sender, $this, $commandLabel, $args);
	}
}
