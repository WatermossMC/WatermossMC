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

final class MultiRecipe extends RecipeWithTypeId
{
	public const TYPE_REPAIR_ITEM = "00000000-0000-0000-0000-000000000001";
	public const TYPE_MAP_EXTENDING = "D392B075-4BA1-40AE-8789-AF868D56F6CE";
	public const TYPE_MAP_EXTENDING_CARTOGRAPHY = "8B36268C-1829-483C-A0F1-993B7156A8F2";
	public const TYPE_MAP_CLONING = "85939755-BA10-4D9D-A4CC-EFB7A8E943C4";
	public const TYPE_MAP_CLONING_CARTOGRAPHY = "442D85ED-8272-4543-A6F1-418F90DED05D";
	public const TYPE_MAP_UPGRADING = "AECD2294-4B94-434B-8667-4499BB2C9327";
	public const TYPE_MAP_UPGRADING_CARTOGRAPHY = "98C84B38-1085-46BD-B1CE-DD38C159E6CC";
	public const TYPE_BOOK_CLONING = "D1CA6B84-338E-4F2F-9C6B-76CC8B4BD98D";
	public const TYPE_BANNER_DUPLICATE = "B5C5D105-75A2-4076-AF2B-923EA2BF4BF0";
	public const TYPE_BANNER_ADD_PATTERN = "D81AAEAF-E172-4440-9225-868DF030D27B";
	public const TYPE_FIREWORKS = "00000000-0000-0000-0000-000000000002";
	public const TYPE_MAP_LOCKING_CARTOGRAPHY = "602234E4-CAC1-4353-8BB7-B1EBFF70024B";

	public function __construct(
		int $typeId,
		private UuidInterface $recipeId,
		private int $recipeNetId
	) {
		parent::__construct($typeId);
	}

	public function getRecipeId() : UuidInterface
	{
		return $this->recipeId;
	}

	public function getRecipeNetId() : int
	{
		return $this->recipeNetId;
	}

	public static function decode(int $typeId, PacketSerializer $in) : self
	{
		$uuid = $in->getUUID();
		$recipeNetId = $in->readRecipeNetId();
		return new self($typeId, $uuid, $recipeNetId);
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putUUID($this->recipeId);
		$out->writeRecipeNetId($this->recipeNetId);
	}
}
