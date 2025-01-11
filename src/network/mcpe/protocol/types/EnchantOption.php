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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

final class EnchantOption
{
	/**
	 * @param Enchant[] $equipActivatedEnchantments
	 * @param Enchant[] $heldActivatedEnchantments
	 * @param Enchant[] $selfActivatedEnchantments
	 */
	public function __construct(
		private int $cost,
		private int $slotFlags,
		private array $equipActivatedEnchantments,
		private array $heldActivatedEnchantments,
		private array $selfActivatedEnchantments,
		private string $name,
		private int $optionId
	) {
	}

	public function getCost() : int
	{
		return $this->cost;
	}

	public function getSlotFlags() : int
	{
		return $this->slotFlags;
	}

	/** @return Enchant[] */
	public function getEquipActivatedEnchantments() : array
	{
		return $this->equipActivatedEnchantments;
	}

	/** @return Enchant[] */
	public function getHeldActivatedEnchantments() : array
	{
		return $this->heldActivatedEnchantments;
	}

	/** @return Enchant[] */
	public function getSelfActivatedEnchantments() : array
	{
		return $this->selfActivatedEnchantments;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getOptionId() : int
	{
		return $this->optionId;
	}

	/**
	 * @return Enchant[]
	 */
	private static function readEnchantList(PacketSerializer $in) : array
	{
		$result = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$result[] = Enchant::read($in);
		}
		return $result;
	}

	/**
	 * @param Enchant[] $list
	 */
	private static function writeEnchantList(PacketSerializer $out, array $list) : void
	{
		$out->putUnsignedVarInt(count($list));
		foreach ($list as $item) {
			$item->write($out);
		}
	}

	public static function read(PacketSerializer $in) : self
	{
		$cost = $in->getUnsignedVarInt();

		$slotFlags = $in->getLInt();
		$equipActivatedEnchants = self::readEnchantList($in);
		$heldActivatedEnchants = self::readEnchantList($in);
		$selfActivatedEnchants = self::readEnchantList($in);

		$name = $in->getString();
		$optionId = $in->readRecipeNetId();

		return new self($cost, $slotFlags, $equipActivatedEnchants, $heldActivatedEnchants, $selfActivatedEnchants, $name, $optionId);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->cost);

		$out->putLInt($this->slotFlags);
		self::writeEnchantList($out, $this->equipActivatedEnchantments);
		self::writeEnchantList($out, $this->heldActivatedEnchantments);
		self::writeEnchantList($out, $this->selfActivatedEnchantments);

		$out->putString($this->name);
		$out->writeRecipeNetId($this->optionId);
	}
}
