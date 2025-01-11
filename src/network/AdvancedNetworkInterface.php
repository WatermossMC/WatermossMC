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

/**
 * Advanced network interfaces have some additional capabilities, such as being able to ban addresses and process raw
 * packets.
 */
interface AdvancedNetworkInterface extends NetworkInterface
{
	/**
	 * Prevents packets received from the IP address getting processed for the given timeout.
	 *
	 * @param int $timeout Seconds
	 */
	public function blockAddress(string $address, int $timeout = 300) : void;

	/**
	 * Unblocks a previously-blocked address.
	 */
	public function unblockAddress(string $address) : void;

	public function setNetwork(Network $network) : void;

	/**
	 * Sends a raw payload to the network interface, bypassing any sessions.
	 */
	public function sendRawPacket(string $address, int $port, string $payload) : void;

	/**
	 * Adds a regex filter for raw packets to this network interface. This filter should be used to check validity of
	 * raw packets before relaying them to the main thread.
	 */
	public function addRawPacketFilter(string $regex) : void;
}
