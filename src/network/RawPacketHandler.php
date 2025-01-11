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

namespace watermossmc\network;

interface RawPacketHandler
{
	/**
	 * Returns a preg_match() compatible regex pattern used to filter packets on this handler. Only packets matching
	 * this pattern will be delivered to the handler.
	 */
	public function getPattern() : string;

	/**
	 * @throws PacketHandlingException
	 */
	public function handle(AdvancedNetworkInterface $interface, string $address, int $port, string $packet) : bool;
}
