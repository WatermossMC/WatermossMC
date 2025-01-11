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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class ChangeDimensionPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CHANGE_DIMENSION_PACKET;

	public int $dimension;
	public Vector3 $position;
	public bool $respawn = false;
	private ?int $loadingScreenId = null;

	/**
	 * @generate-create-func
	 */
	public static function create(int $dimension, Vector3 $position, bool $respawn, ?int $loadingScreenId) : self
	{
		$result = new self();
		$result->dimension = $dimension;
		$result->position = $position;
		$result->respawn = $respawn;
		$result->loadingScreenId = $loadingScreenId;
		return $result;
	}

	public function getLoadingScreenId() : ?int
	{
		return $this->loadingScreenId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->dimension = $in->getVarInt();
		$this->position = $in->getVector3();
		$this->respawn = $in->getBool();
		$this->loadingScreenId = $in->readOptional(fn () => $in->getLInt());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->dimension);
		$out->putVector3($this->position);
		$out->putBool($this->respawn);
		$out->writeOptional($this->loadingScreenId, $out->putLInt(...));
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleChangeDimension($this);
	}
}
