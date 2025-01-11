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
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

class SyncActorPropertyPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SYNC_ACTOR_PROPERTY_PACKET;

	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $nbt;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(CacheableNbt $nbt) : self
	{
		$result = new self();
		$result->nbt = $nbt;
		return $result;
	}

	/** @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public function getNbt() : CacheableNbt
	{
		return $this->nbt;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->put($this->nbt->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSyncActorProperty($this);
	}
}
