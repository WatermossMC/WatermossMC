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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\nbt\LittleEndianNbtSerializer;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\TreeRoot;
use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;
use function strlen;

/**
 * Wrapper class for extra data on ItemStacks.
 * The data is normally provided as a raw string (not automatically decoded).
 * This class is just a DTO for PacketSerializer to use when encoding/decoding ItemStacks.
 */
class ItemStackExtraData
{
	/**
	 * @param string[] $canPlaceOn
	 * @param string[] $canDestroy
	 */
	public function __construct(
		private ?CompoundTag $nbt,
		private array $canPlaceOn,
		private array $canDestroy
	) {
	}

	/**
	 * @return string[]
	 */
	public function getCanPlaceOn() : array
	{
		return $this->canPlaceOn;
	}

	/**
	 * @return string[]
	 */
	public function getCanDestroy() : array
	{
		return $this->canDestroy;
	}

	public function getNbt() : ?CompoundTag
	{
		return $this->nbt;
	}

	public static function read(PacketSerializer $in) : self
	{
		$nbtLen = $in->getLShort();

		/** @var CompoundTag|null $compound */
		$compound = null;
		if ($nbtLen === 0xffff) {
			$nbtDataVersion = $in->getByte();
			if ($nbtDataVersion !== 1) {
				throw new PacketDecodeException("Unexpected NBT data version $nbtDataVersion");
			}
			$offset = $in->getOffset();
			try {
				$compound = (new LittleEndianNbtSerializer())->read($in->getBuffer(), $offset, 512)->mustGetCompoundTag();
			} catch (NbtDataException $e) {
				throw PacketDecodeException::wrap($e, "Failed decoding NBT root");
			} finally {
				$in->setOffset($offset);
			}
		} elseif ($nbtLen !== 0) {
			throw new PacketDecodeException("Unexpected fake NBT length $nbtLen");
		}

		$canPlaceOn = [];
		for ($i = 0, $canPlaceOnCount = $in->getLInt(); $i < $canPlaceOnCount; ++$i) {
			$canPlaceOn[] = $in->get($in->getLShort());
		}

		$canDestroy = [];
		for ($i = 0, $canDestroyCount = $in->getLInt(); $i < $canDestroyCount; ++$i) {
			$canDestroy[] = $in->get($in->getLShort());
		}

		return new self($compound, $canPlaceOn, $canDestroy);
	}

	public function write(PacketSerializer $out) : void
	{
		if ($this->nbt !== null) {
			$out->putLShort(0xffff);
			$out->putByte(1); //TODO: NBT data version (?)
			$out->put((new LittleEndianNbtSerializer())->write(new TreeRoot($this->nbt)));
		} else {
			$out->putLShort(0);
		}

		$out->putLInt(count($this->canPlaceOn));
		foreach ($this->canPlaceOn as $entry) {
			$out->putLShort(strlen($entry));
			$out->put($entry);
		}
		$out->putLInt(count($this->canDestroy));
		foreach ($this->canDestroy as $entry) {
			$out->putLShort(strlen($entry));
			$out->put($entry);
		}
	}
}
