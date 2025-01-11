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

/**
 * Unclear purpose, not used in vanilla Bedrock. Seems to be related to a new Minecraft "editor" edition or mode.
 */
class EditorNetworkPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::EDITOR_NETWORK_PACKET;

	private bool $isRouteToManager;
	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $payload;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $payload
	 */
	public static function create(bool $isRouteToManager, CacheableNbt $payload) : self
	{
		$result = new self();
		$result->isRouteToManager = $isRouteToManager;
		$result->payload = $payload;
		return $result;
	}

	/** @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public function getPayload() : CacheableNbt
	{
		return $this->payload;
	}

	public function isRouteToManager() : bool
	{
		return $this->isRouteToManager;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->isRouteToManager = $in->getBool();
		$this->payload = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->isRouteToManager);
		$out->put($this->payload->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleEditorNetwork($this);
	}
}
