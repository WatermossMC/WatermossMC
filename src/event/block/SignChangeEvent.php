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

namespace watermossmc\event\block;

use watermossmc\block\BaseSign;
use watermossmc\block\utils\SignText;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\player\Player;

/**
 * Called when a sign's text is changed by a player.
 */
class SignChangeEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		private BaseSign $sign,
		private Player $player,
		private SignText $text
	) {
		parent::__construct($sign);
	}

	public function getSign() : BaseSign
	{
		return $this->sign;
	}

	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * Returns the text currently on the sign.
	 */
	public function getOldText() : SignText
	{
		return $this->sign->getText();
	}

	/**
	 * Returns the text which will be on the sign after the event.
	 */
	public function getNewText() : SignText
	{
		return $this->text;
	}

	/**
	 * Sets the text to be written on the sign after the event.
	 */
	public function setNewText(SignText $text) : void
	{
		$this->text = $text;
	}
}
