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

namespace watermossmc\console;

use watermossmc\command\CommandSender;
use watermossmc\lang\Language;
use watermossmc\lang\Translatable;
use watermossmc\permission\DefaultPermissions;
use watermossmc\permission\PermissibleBase;
use watermossmc\permission\PermissibleDelegateTrait;
use watermossmc\Server;
use watermossmc\utils\Terminal;
use watermossmc\utils\TextFormat;

use function explode;
use function trim;

use const PHP_INT_MAX;

class ConsoleCommandSender implements CommandSender
{
	use PermissibleDelegateTrait;

	/** @phpstan-var positive-int|null */
	protected ?int $lineHeight = null;

	public function __construct(
		private Server $server,
		private Language $language
	) {
		$this->perm = new PermissibleBase([DefaultPermissions::ROOT_CONSOLE => true]);
	}

	public function getServer() : Server
	{
		return $this->server;
	}

	public function getLanguage() : Language
	{
		return $this->language;
	}

	public function sendMessage(Translatable|string $message) : void
	{
		if ($message instanceof Translatable) {
			$message = $this->getLanguage()->translate($message);
		}

		foreach (explode("\n", trim($message)) as $line) {
			Terminal::writeLine(TextFormat::GREEN . "Command output | " . TextFormat::addBase(TextFormat::WHITE, $line));
		}
	}

	public function getName() : string
	{
		return "CONSOLE";
	}

	public function getScreenLineHeight() : int
	{
		return $this->lineHeight ?? PHP_INT_MAX;
	}

	public function setScreenLineHeight(?int $height) : void
	{
		if ($height !== null && $height < 1) {
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}
}
