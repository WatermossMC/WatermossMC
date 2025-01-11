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

/** This is used for PlayerAuthInput packet when the flags include PERFORM_BLOCK_ACTIONS */
final class PlayerBlockActionWithBlockInfo implements PlayerBlockAction
{
	public function __construct(
		private int $actionType,
		private BlockPosition $blockPosition,
		private int $face
	) {
		if (!self::isValidActionType($actionType)) {
			throw new \InvalidArgumentException("Invalid action type for " . self::class);
		}
	}

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getBlockPosition() : BlockPosition
	{
		return $this->blockPosition;
	}

	public function getFace() : int
	{
		return $this->face;
	}

	public static function read(PacketSerializer $in, int $actionType) : self
	{
		$blockPosition = $in->getSignedBlockPosition();
		$face = $in->getVarInt();
		return new self($actionType, $blockPosition, $face);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putSignedBlockPosition($this->blockPosition);
		$out->putVarInt($this->face);
	}

	public static function isValidActionType(int $actionType) : bool
	{
		return match($actionType) {
			PlayerAction::ABORT_BREAK,
			PlayerAction::START_BREAK,
			PlayerAction::CRACK_BREAK,
			PlayerAction::PREDICT_DESTROY_BLOCK,
			PlayerAction::CONTINUE_DESTROY_BLOCK => true,
			default => false
		};
	}
}
