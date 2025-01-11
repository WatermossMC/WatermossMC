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

use watermossmc\command\utils\InvalidCommandSyntaxException;
use watermossmc\plugin\Plugin;
use watermossmc\plugin\PluginOwned;

final class PluginCommand extends Command implements PluginOwned
{
	public function __construct(
		string $name,
		private Plugin $owner,
		private CommandExecutor $executor
	) {
		parent::__construct($name);
		$this->usageMessage = "";
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{

		if (!$this->owner->isEnabled()) {
			return false;
		}

		$success = $this->executor->onCommand($sender, $this, $commandLabel, $args);

		if (!$success && $this->usageMessage !== "") {
			throw new InvalidCommandSyntaxException();
		}

		return $success;
	}

	public function getOwningPlugin() : Plugin
	{
		return $this->owner;
	}

	public function getExecutor() : CommandExecutor
	{
		return $this->executor;
	}

	public function setExecutor(CommandExecutor $executor) : void
	{
		$this->executor = $executor;
	}
}
