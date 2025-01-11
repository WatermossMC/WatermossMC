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

namespace watermossmc\event\player;

use watermossmc\command\CommandSender;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\player\chat\ChatFormatter;
use watermossmc\player\Player;
use watermossmc\utils\Utils;

/**
 * Called when a player chats something
 */
class PlayerChatEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param CommandSender[] $recipients
	 */
	public function __construct(
		Player $player,
		protected string $message,
		protected array $recipients,
		protected ChatFormatter $formatter
	) {
		$this->player = $player;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	public function setMessage(string $message) : void
	{
		$this->message = $message;
	}

	/**
	 * Changes the player that is sending the message
	 */
	public function setPlayer(Player $player) : void
	{
		$this->player = $player;
	}

	public function getFormatter() : ChatFormatter
	{
		return $this->formatter;
	}

	public function setFormatter(ChatFormatter $formatter) : void
	{
		$this->formatter = $formatter;
	}

	/**
	 * @return CommandSender[]
	 */
	public function getRecipients() : array
	{
		return $this->recipients;
	}

	/**
	 * @param CommandSender[] $recipients
	 */
	public function setRecipients(array $recipients) : void
	{
		Utils::validateArrayValueType($recipients, function (CommandSender $_) : void {});
		$this->recipients = $recipients;
	}
}
