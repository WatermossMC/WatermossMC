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

namespace watermossmc\event\server;

use watermossmc\command\CommandSender;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * Called when any CommandSender runs a command, before it is parsed.
 *
 * This can be used for logging commands, or preprocessing the command string to add custom features (e.g. selectors).
 *
 * WARNING: DO NOT use this to block commands. Many commands have aliases.
 * For example, /version can also be invoked using /ver or /about.
 * To prevent command senders from using certain commands, deny them permission to use the commands you don't want them
 * to have access to.
 *
 * @see Permissible::addAttachment()
 *
 * The message DOES NOT begin with a slash.
 */
class CommandEvent extends ServerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		protected CommandSender $sender,
		protected string $command
	) {
	}

	public function getSender() : CommandSender
	{
		return $this->sender;
	}

	public function getCommand() : string
	{
		return $this->command;
	}

	public function setCommand(string $command) : void
	{
		$this->command = $command;
	}
}
