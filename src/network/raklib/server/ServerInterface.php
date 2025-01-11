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

use watermossmc\network\raklib\protocol\EncapsulatedPacket;

interface ServerInterface
{
	public function sendEncapsulated(int $sessionId, EncapsulatedPacket $packet, bool $immediate = false) : void;

	public function sendRaw(string $address, int $port, string $payload) : void;

	public function closeSession(int $sessionId) : void;

	public function setName(string $name) : void;

	public function setPortCheck(bool $value) : void;

	public function setPacketsPerTickLimit(int $limit) : void;

	public function blockAddress(string $address, int $timeout) : void;

	public function unblockAddress(string $address) : void;

	public function addRawPacketFilter(string $regex) : void;
}
