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

final class ShapedRecipe extends RecipeWithTypeId
{
	private string $blockName;

	/**
	 * @param RecipeIngredient[][] $input
	 * @param ItemStack[]          $output
	 * @phpstan-param list<list<RecipeIngredient>> $input
	 * @phpstan-param list<ItemStack> $output
	 */
	public function __construct(
		int $typeId,
		private string $recipeId,
		private array $input,
		private array $output,
		private UuidInterface $uuid,
		string $blockType, //TODO: rename this
		private int $priority,
		private bool $symmetric,
		private RecipeUnlockingRequirement $unlockingRequirement,
		private int $recipeNetId
	) {
		parent::__construct($typeId);
		$rows = count($input);
		if ($rows < 1 || $rows > 3) {
			throw new \InvalidArgumentException("Expected 1, 2 or 3 input rows");
		}
		$columns = null;
		foreach ($input as $rowNumber => $row) {
			if ($columns === null) {
				$columns = count($row);
			} elseif (count($row) !== $columns) {
				throw new \InvalidArgumentException("Expected each row to be $columns columns, but have " . count($row) . " in row $rowNumber");
			}
		}
		$this->blockName = $blockType;
	}

	public function getRecipeId() : string
	{
		return $this->recipeId;
	}

	public function getWidth() : int
	{
		return count($this->input[0]);
	}

	public function getHeight() : int
	{
		return count($this->input);
	}

	/**
	 * @return RecipeIngredient[][]
	 * @phpstan-return list<list<RecipeIngredient>>
	 */
	public function getInput() : array
	{
		return $this->input;
	}

	/**
	 * @return ItemStack[]
	 * @phpstan-return list<ItemStack>
	 */
	public function getOutput() : array
	{
		return $this->output;
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

	public function isSymmetric() : bool
	{
		return $this->symmetric;
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
		$width = $in->getVarInt();
		$height = $in->getVarInt();
		$input = [];
		for ($row = 0; $row < $height; ++$row) {
			for ($column = 0; $column < $width; ++$column) {
				$input[$row][$column] = $in->getRecipeIngredient();
			}
		}

		$output = [];
		for ($k = 0, $resultCount = $in->getUnsignedVarInt(); $k < $resultCount; ++$k) {
			$output[] = $in->getItemStackWithoutStackId();
		}
		$uuid = $in->getUUID();
		$block = $in->getString();
		$priority = $in->getVarInt();
		$symmetric = $in->getBool();
		$unlockingRequirement = RecipeUnlockingRequirement::read($in);

		$recipeNetId = $in->readRecipeNetId();

		return new self($recipeType, $recipeId, $input, $output, $uuid, $block, $priority, $symmetric, $unlockingRequirement, $recipeNetId);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putString($this->recipeId);
		$out->putVarInt($this->getWidth());
		$out->putVarInt($this->getHeight());
		foreach ($this->input as $row) {
			foreach ($row as $ingredient) {
				$out->putRecipeIngredient($ingredient);
			}
		}

		$out->putUnsignedVarInt(count($this->output));
		foreach ($this->output as $item) {
			$out->putItemStackWithoutStackId($item);
		}

		$out->putUUID($this->uuid);
		$out->putString($this->blockName);
		$out->putVarInt($this->priority);
		$out->putBool($this->symmetric);
		$this->unlockingRequirement->write($out);

		$out->writeRecipeNetId($this->recipeNetId);
	}
}
