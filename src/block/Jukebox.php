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

use watermossmc\block\tile\Jukebox as JukeboxTile;
use watermossmc\item\Item;
use watermossmc\item\Record;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\RecordSound;
use watermossmc\world\sound\RecordStopSound;

class Jukebox extends Opaque
{
	private ?Record $record = null;

	public function getFuelTime() : int
	{
		return 300;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player instanceof Player) {
			if ($this->record !== null) {
				$this->ejectRecord();
			} elseif ($item instanceof Record) {
				$player->sendJukeboxPopup(KnownTranslationFactory::record_nowPlaying($item->getRecordType()->getTranslatableName()));
				$this->insertRecord($item->pop());
			}
		}

		$this->position->getWorld()->setBlock($this->position, $this);

		return true;
	}

	public function getRecord() : ?Record
	{
		return $this->record;
	}

	public function ejectRecord() : void
	{
		if ($this->record !== null) {
			$this->position->getWorld()->dropItem($this->position->add(0.5, 1, 0.5), $this->record);
			$this->record = null;
			$this->stopSound();
		}
	}

	public function insertRecord(Record $record) : void
	{
		if ($this->record === null) {
			$this->record = $record;
			$this->startSound();
		}
	}

	public function startSound() : void
	{
		if ($this->record !== null) {
			$this->position->getWorld()->addSound($this->position, new RecordSound($this->record->getRecordType()));
		}
	}

	public function stopSound() : void
	{
		$this->position->getWorld()->addSound($this->position, new RecordStopSound());
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->stopSound();
		return parent::onBreak($item, $player, $returnedItems);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$drops = parent::getDropsForCompatibleTool($item);
		if ($this->record !== null) {
			$drops[] = $this->record;
		}
		return $drops;
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$jukebox = $this->position->getWorld()->getTile($this->position);
		if ($jukebox instanceof JukeboxTile) {
			$this->record = $jukebox->getRecord();
		}

		return $this;
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$jukebox = $this->position->getWorld()->getTile($this->position);
		if ($jukebox instanceof JukeboxTile) {
			$jukebox->setRecord($this->record);
		}
	}

	//TODO: Jukebox has redstone effects, they are not implemented.
}
