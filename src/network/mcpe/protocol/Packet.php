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

interface Packet
{
	public function pid() : int;

	public function getName() : string;

	public function canBeSentBeforeLogin() : bool;

	/**
	 * @throws PacketDecodeException
	 */
	public function decode(PacketSerializer $in) : void;

	public function encode(PacketSerializer $out) : void;

	/**
	 * Performs handling for this packet. Usually you'll want an appropriately named method in the session handler for
	 * this.
	 *
	 * This method returns a bool to indicate whether the packet was handled or not. If the packet was unhandled, a
	 * debug message will be logged with a hexdump of the packet.
	 *
	 * Typically this method returns the return value of the handler in the supplied PacketHandler. See other packets
	 * for examples how to implement this.
	 *
	 * @return bool true if the packet was handled successfully, false if not.
	 * @throws PacketDecodeException if broken data was found in the packet
	 */
	public function handle(PacketHandlerInterface $handler) : bool;
}
