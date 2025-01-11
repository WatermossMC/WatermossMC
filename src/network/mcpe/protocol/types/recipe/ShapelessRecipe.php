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

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStack;

use function count;

final class ShapelessRecipe extends RecipeWithTypeId
{
	/**
	 * @param RecipeIngredient[] $inputs
	 * @param ItemStack[]        $outputs
	 * @phpstan-param list<RecipeIngredient> $inputs
	 * @phpstan-param list<ItemStack> $outputs
	 */
	public function __construct(
		int $typeId,
		private string $recipeId,
		private array $inputs,
		private array $outputs,
		private UuidInterface $uuid,
		private string $blockName,
		private int $priority,
		private RecipeUnlockingRequirement $unlockingRequirement,
		private int $recipeNetId
	) {
		parent::__construct($typeId);
	}

	public function getRecipeId() : string
	{
		return $this->recipeId;
	}

	/**
	 * @return RecipeIngredient[]
	 * @phpstan-return list<RecipeIngredient>
	 */
	public function getInputs() : array
	{
		return $this->inputs;
	}

	/**
	 * @return ItemStack[]
	 * @phpstan-return list<ItemStack>
	 */
	public function getOutputs() : array
	{
		return $this->outputs;
	}

	public function getUuid() : UuidInterface
	{
		return $this->uuid;
	}

	public function getBlockName() : string
	{
		return $this->blockName;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public function getUnlockingRequirement() : RecipeUnlockingRequirement
	{
		return $this->unlockingRequirement;
	}

	public function getRecipeNetId() : int
	{
		return $this->recipeNetId;
	}

	public static function decode(int $recipeType, PacketSerializer $in) : self
	{
		$recipeId = $in->getString();
		$input = [];
		for ($j = 0, $ingredientCount = $in->getUnsignedVarInt(); $j < $ingredientCount; ++$j) {
			$input[] = $in->getRecipeIngredient();
		}
		$output = [];
		for ($k = 0, $resultCount = $in->getUnsignedVarInt(); $k < $resultCount; ++$k) {
			$output[] = $in->getItemStackWithoutStackId();
		}
		$uuid = $in->getUUID();
		$block = $in->getString();
		$priority = $in->getVarInt();
		$unlockingRequirement = RecipeUnlockingRequirement::read($in);

		$recipeNetId = $in->readRecipeNetId();

		return new self($recipeType, $recipeId, $input, $output, $uuid, $block, $priority, $unlockingRequirement, $recipeNetId);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putString($this->recipeId);
		$out->putUnsignedVarInt(count($this->inputs));
		foreach ($this->inputs as $item) {
			$out->putRecipeIngredient($item);
		}

		$out->putUnsignedVarInt(count($this->outputs));
		foreach ($this->outputs as $item) {
			$out->putItemStackWithoutStackId($item);
		}

		$out->putUUID($this->uuid);
		$out->putString($this->blockName);
		$out->putVarInt($this->priority);
		$this->unlockingRequirement->write($out);

		$out->writeRecipeNetId($this->recipeNetId);
	}
}
