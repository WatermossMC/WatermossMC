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

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\WritableBookBase;
use watermossmc\player\Player;

class PlayerEditBookEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public const ACTION_REPLACE_PAGE = 0;
	public const ACTION_ADD_PAGE = 1;
	public const ACTION_DELETE_PAGE = 2;
	public const ACTION_SWAP_PAGES = 3;
	public const ACTION_SIGN_BOOK = 4;

	/**
	 * @param int[] $modifiedPages
	 */
	public function __construct(
		Player $player,
		private WritableBookBase $oldBook,
		private WritableBookBase $newBook,
		private int $action,
		private array $modifiedPages
	) {
		$this->player = $player;
	}

	/**
	 * Returns the action of the event.
	 */
	public function getAction() : int
	{
		return $this->action;
	}

	/**
	 * Returns the book before it was modified.
	 */
	public function getOldBook() : WritableBookBase
	{
		return $this->oldBook;
	}

	/**
	 * Returns the book after it was modified.
	 * The new book may be a written book, if the book was signed.
	 */
	public function getNewBook() : WritableBookBase
	{
		return $this->newBook;
	}

	/**
	 * Sets the new book as the given instance.
	 */
	public function setNewBook(WritableBookBase $book) : void
	{
		$this->newBook = $book;
	}

	/**
	 * Returns an array containing the page IDs of modified pages.
	 *
	 * @return int[]
	 */
	public function getModifiedPages() : array
	{
		return $this->modifiedPages;
	}
}
