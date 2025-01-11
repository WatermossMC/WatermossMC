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

final class PlayerMovementSettings
{
	public function __construct(
		private ServerAuthMovementMode $movementType,
		private int $rewindHistorySize,
		private bool $serverAuthoritativeBlockBreaking
	) {
	}

	public function getMovementType() : ServerAuthMovementMode
	{
		return $this->movementType;
	}

	public function getRewindHistorySize() : int
	{
		return $this->rewindHistorySize;
	}

	public function isServerAuthoritativeBlockBreaking() : bool
	{
		return $this->serverAuthoritativeBlockBreaking;
	}

	public static function read(PacketSerializer $in) : self
	{
		$movementType = ServerAuthMovementMode::fromPacket($in->getVarInt());
		$rewindHistorySize = $in->getVarInt();
		$serverAuthBlockBreaking = $in->getBool();
		return new self($movementType, $rewindHistorySize, $serverAuthBlockBreaking);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->movementType->value);
		$out->putVarInt($this->rewindHistorySize);
		$out->putBool($this->serverAuthoritativeBlockBreaking);
	}
}
