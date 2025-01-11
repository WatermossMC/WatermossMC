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

namespace watermossmc\block;

use watermossmc\block\tile\Lectern as TileLectern;
use watermossmc\block\utils\FacesOppositePlacingPlayerTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\item\WritableBookBase;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\LecternPlaceBookSound;

use function count;

class Lectern extends Transparent
{
	use FacesOppositePlacingPlayerTrait;

	protected int $viewedPage = 0;
	protected ?WritableBookBase $book = null;

	protected bool $producingSignal = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->producingSignal);
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if ($tile instanceof TileLectern) {
			$this->viewedPage = $tile->getViewedPage();
			$this->book = $tile->getBook();
		}

		return $this;
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if ($tile instanceof TileLectern) {
			$tile->setViewedPage($this->viewedPage);
			$tile->setBook($this->book);
		}
	}

	public function getFlammability() : int
	{
		return 30;
	}

	public function getDrops(Item $item) : array
	{
		$drops = parent::getDrops($item);
		if ($this->book !== null) {
			$drops[] = clone $this->book;
		}

		return $drops;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->trim(Facing::UP, 0.1)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function isProducingSignal() : bool
	{
		return $this->producingSignal;
	}

	/** @return $this */
	public function setProducingSignal(bool $producingSignal) : self
	{
		$this->producingSignal = $producingSignal;
		return $this;
	}

	public function getViewedPage() : int
	{
		return $this->viewedPage;
	}

	/** @return $this */
	public function setViewedPage(int $viewedPage) : self
	{
		$this->viewedPage = $viewedPage;
		return $this;
	}

	public function getBook() : ?WritableBookBase
	{
		return $this->book !== null ? clone $this->book : null;
	}

	/** @return $this */
	public function setBook(?WritableBookBase $book) : self
	{
		$this->book = $book !== null && !$book->isNull() ? (clone $book)->setCount(1) : null;
		$this->viewedPage = 0;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->book === null && $item instanceof WritableBookBase) {
			$world = $this->position->getWorld();
			$world->setBlock($this->position, $this->setBook($item));
			$world->addSound($this->position, new LecternPlaceBookSound());
			$item->pop();
		}
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		if ($this->book !== null) {
			$world = $this->position->getWorld();
			$world->dropItem($this->position->up(), $this->book);
			$world->setBlock($this->position, $this->setBook(null));
		}
		return false;
	}

	public function onPageTurn(int $newPage) : bool
	{
		if ($newPage === $this->viewedPage) {
			return true;
		}
		if ($this->book === null || $newPage >= count($this->book->getPages()) || $newPage < 0) {
			return false;
		}

		$this->viewedPage = $newPage;
		$world = $this->position->getWorld();
		if (!$this->producingSignal) {
			$this->producingSignal = true;
			$world->scheduleDelayedBlockUpdate($this->position, 1);
		}

		$world->setBlock($this->position, $this);

		return true;
	}

	public function onScheduledUpdate() : void
	{
		if ($this->producingSignal) {
			$this->producingSignal = false;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}
}
