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

namespace watermossmc\block\tile;

use watermossmc\item\Item;
use watermossmc\item\Record;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\world\sound\RecordStopSound;

class Jukebox extends Spawnable
{
	private const TAG_RECORD = "RecordItem"; //Item CompoundTag

	private ?Record $record = null;

	public function getRecord() : ?Record
	{
		return $this->record;
	}

	public function setRecord(?Record $record) : void
	{
		$this->record = $record;
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		if (($tag = $nbt->getCompoundTag(self::TAG_RECORD)) !== null) {
			$record = Item::nbtDeserialize($tag);
			if ($record instanceof Record) {
				$this->record = $record;
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		if ($this->record !== null) {
			$nbt->setTag(self::TAG_RECORD, $this->record->nbtSerialize());
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		//this is needed for the note particles to show on the client side
		if ($this->record !== null) {
			$nbt->setTag(self::TAG_RECORD, TypeConverter::getInstance()->getItemTranslator()->toNetworkNbt($this->record));
		}
	}

	protected function onBlockDestroyedHook() : void
	{
		$this->position->getWorld()->addSound($this->position, new RecordStopSound());
	}
}
