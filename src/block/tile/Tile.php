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

use watermossmc\block\Block;
use watermossmc\item\Item;
use watermossmc\math\Vector3;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\timings\Timings;
use watermossmc\timings\TimingsHandler;
use watermossmc\VersionInfo;
use watermossmc\world\Position;
use watermossmc\world\World;

use function get_class;

abstract class Tile
{
	public const TAG_ID = "id";
	public const TAG_X = "x";
	public const TAG_Y = "y";
	public const TAG_Z = "z";

	protected Position $position;
	public bool $closed = false;
	protected TimingsHandler $timings;

	public function __construct(World $world, Vector3 $pos)
	{
		$this->position = Position::fromObject($pos, $world);
		$this->timings = Timings::getTileEntityTimings($this);
	}

	/**
	 * @internal
	 * @throws NbtDataException
	 * Reads additional data from the CompoundTag on tile creation.
	 */
	abstract public function readSaveData(CompoundTag $nbt) : void;

	/**
	 * Writes additional save data to a CompoundTag, not including generic things like ID and coordinates.
	 */
	abstract protected function writeSaveData(CompoundTag $nbt) : void;

	public function saveNBT() : CompoundTag
	{
		$nbt = CompoundTag::create()
			->setString(self::TAG_ID, TileFactory::getInstance()->getSaveId(get_class($this)))
			->setInt(self::TAG_X, $this->position->getFloorX())
			->setInt(self::TAG_Y, $this->position->getFloorY())
			->setInt(self::TAG_Z, $this->position->getFloorZ())
			->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);
		$this->writeSaveData($nbt);

		return $nbt;
	}

	public function getCleanedNBT() : ?CompoundTag
	{
		$this->writeSaveData($tag = new CompoundTag());
		return $tag->getCount() > 0 ? $tag : null;
	}

	/**
	 * @internal
	 *
	 * @throws \RuntimeException
	 */
	public function copyDataFromItem(Item $item) : void
	{
		if (($blockNbt = $item->getCustomBlockData()) !== null) { //TODO: check item root tag (MCPE doesn't use BlockEntityTag)
			$this->readSaveData($blockNbt);
		}
	}

	public function getBlock() : Block
	{
		return $this->position->getWorld()->getBlock($this->position);
	}

	public function getPosition() : Position
	{
		return $this->position;
	}

	public function isClosed() : bool
	{
		return $this->closed;
	}

	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Called when the tile's block is destroyed.
	 */
	final public function onBlockDestroyed() : void
	{
		$this->onBlockDestroyedHook();
		$this->close();
	}

	/**
	 * Override this method to do actions you need to do when this tile is destroyed due to block being broken.
	 */
	protected function onBlockDestroyedHook() : void
	{

	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->closed = true;

			if ($this->position->isValid()) {
				$this->position->getWorld()->removeTile($this);
			}
		}
	}
}
