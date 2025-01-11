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

namespace watermossmc\network\mcpe\protocol\types\recipe;

use watermossmc\network\mcpe\protocol\CraftingDataPacket;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStack;

final class FurnaceRecipe extends RecipeWithTypeId
{
	public function __construct(
		int $typeId,
		private int $inputId,
		private ?int $inputMeta,
		private ItemStack $result,
		private string $blockName
	) {
		parent::__construct($typeId);
	}

	public function getInputId() : int
	{
		return $this->inputId;
	}

	public function getInputMeta() : ?int
	{
		return $this->inputMeta;
	}

	public function getResult() : ItemStack
	{
		return $this->result;
	}

	public function getBlockName() : string
	{
		return $this->blockName;
	}

	public static function decode(int $typeId, PacketSerializer $in) : self
	{
		$inputId = $in->getVarInt();
		$inputData = null;
		if ($typeId === CraftingDataPacket::ENTRY_FURNACE_DATA) {
			$inputData = $in->getVarInt();
		}
		$output = $in->getItemStackWithoutStackId();
		$block = $in->getString();

		return new self($typeId, $inputId, $inputData, $output, $block);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putVarInt($this->inputId);
		if ($this->getTypeId() === CraftingDataPacket::ENTRY_FURNACE_DATA) {
			$out->putVarInt($this->inputMeta);
		}
		$out->putItemStackWithoutStackId($this->result);
		$out->putString($this->blockName);
	}
}
