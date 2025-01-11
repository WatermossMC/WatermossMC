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

use watermossmc\command\CommandSender;
use watermossmc\lang\Language;
use watermossmc\lang\Translatable;
use watermossmc\permission\PermissibleBase;
use watermossmc\permission\PermissibleDelegateTrait;
use watermossmc\Server;

use const PHP_INT_MAX;

/**
 * Forwards any messages it receives via sendMessage() to the given logger. Used for forwarding chat messages and
 * command audit log messages to the server log file.
 *
 * Unfortunately, broadcast subscribers are currently required to implement CommandSender, so this class has to include
 * a lot of useless methods.
 */
final class BroadcastLoggerForwarder implements CommandSender
{
	use PermissibleDelegateTrait;

	public function __construct(
		private Server $server, //annoying useless dependency
		private \Logger $logger,
		private Language $language
	) {
		//this doesn't need any permissions
		$this->perm = new PermissibleBase([]);
	}

	public function getLanguage() : Language
	{
		return $this->language;
	}

	public function sendMessage(Translatable|string $message) : void
	{
		if ($message instanceof Translatable) {
			$this->logger->info($this->language->translate($message));
		} else {
			$this->logger->info($message);
		}
	}

	public function getServer() : Server
	{
		return $this->server;
	}

	public function getName() : string
	{
		return "Broadcast Logger Forwarder";
	}

	public function getScreenLineHeight() : int
	{
		return PHP_INT_MAX;
	}

	public function setScreenLineHeight(?int $height) : void
	{
		//NOOP
	}
}
