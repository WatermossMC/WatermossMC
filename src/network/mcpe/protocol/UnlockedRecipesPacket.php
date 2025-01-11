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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

class UnlockedRecipesPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UNLOCKED_RECIPES_PACKET;

	public const TYPE_EMPTY = 0;
	public const TYPE_INITIALLY_UNLOCKED = 1;
	public const TYPE_NEWLY_UNLOCKED = 2;
	public const TYPE_REMOVE = 3;
	public const TYPE_REMOVE_ALL = 4;

	private int $type;
	/** @var string[] */
	private array $recipes;

	/**
	 * @generate-create-func
	 * @param string[] $recipes
	 */
	public static function create(int $type, array $recipes) : self
	{
		$result = new self();
		$result->type = $type;
		$result->recipes = $recipes;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	/**
	 * @return string[]
	 */
	public function getRecipes() : array
	{
		return $this->recipes;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getLInt();
		$this->recipes = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++) {
			$this->recipes[] = $in->getString();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLInt($this->type);
		$out->putUnsignedVarInt(count($this->recipes));
		foreach ($this->recipes as $recipe) {
			$out->putString($recipe);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUnlockedRecipes($this);
	}
}
