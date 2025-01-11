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
use watermossmc\network\mcpe\protocol\types\hud\LoadingScreenType;

class ServerboundLoadingScreenPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_LOADING_SCREEN_PACKET;

	private LoadingScreenType $loadingScreenType;
	private ?int $loadingScreenId = null;

	/**
	 * @generate-create-func
	 */
	public static function create(LoadingScreenType $loadingScreenType, ?int $loadingScreenId) : self
	{
		$result = new self();
		$result->loadingScreenType = $loadingScreenType;
		$result->loadingScreenId = $loadingScreenId;
		return $result;
	}

	public function getLoadingScreenType() : LoadingScreenType
	{
		return $this->loadingScreenType;
	}

	public function getLoadingScreenId() : ?int
	{
		return $this->loadingScreenId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->loadingScreenType = LoadingScreenType::fromPacket($in->getVarInt());
		$this->loadingScreenId = $in->readOptional(fn () => $in->getLInt());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->loadingScreenType->value);
		$out->writeOptional($this->loadingScreenId, $out->putLInt(...));
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleServerboundLoadingScreen($this);
	}
}
