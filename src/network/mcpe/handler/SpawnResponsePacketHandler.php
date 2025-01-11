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

namespace watermossmc\network\mcpe\handler;

use watermossmc\network\mcpe\protocol\PlayerAuthInputPacket;
use watermossmc\network\mcpe\protocol\PlayerSkinPacket;
use watermossmc\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;

final class SpawnResponsePacketHandler extends PacketHandler
{
	/**
	 * @phpstan-param \Closure() : void $responseCallback
	 */
	public function __construct(private \Closure $responseCallback)
	{
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool
	{
		($this->responseCallback)();
		return true;
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool
	{
		//TODO: REMOVE THIS
		//As of 1.19.60, we receive this packet during pre-spawn for no obvious reason. The skin is still sent in the
		//login packet, so we can ignore this one. If unhandled, this packet makes a huge debug spam in the log.
		return true;
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool
	{
		//the client will send this every tick once we start sending chunks, but we don't handle it in this stage
		//this is very spammy so we filter it out
		return true;
	}
}
