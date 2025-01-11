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

namespace watermossmc\network\raklib\server;

use watermossmc\network\raklib\generic\DisconnectReason;

interface ServerEventListener
{
	public function onClientConnect(int $sessionId, string $address, int $port, int $clientID) : void;

	/**
	 * @param int $reason one of the DisconnectReason constants
	 * @phpstan-param DisconnectReason::* $reason
	 *
	 * @see DisconnectReason
	 */
	public function onClientDisconnect(int $sessionId, int $reason) : void;

	public function onPacketReceive(int $sessionId, string $packet) : void;

	public function onRawPacketReceive(string $address, int $port, string $payload) : void;

	public function onPacketAck(int $sessionId, int $identifierACK) : void;

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void;

	public function onPingMeasure(int $sessionId, int $pingMS) : void;
}
