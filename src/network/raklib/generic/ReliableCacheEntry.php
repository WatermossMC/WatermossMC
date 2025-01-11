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

namespace watermossmc\network\raklib\generic;

use watermossmc\network\raklib\protocol\EncapsulatedPacket;

use function microtime;

final class ReliableCacheEntry
{
	private float $timestamp;

	/**
	 * @param EncapsulatedPacket[] $packets
	 */
	public function __construct(
		private array $packets
	) {
		$this->timestamp = microtime(true);
	}

	/**
	 * @return EncapsulatedPacket[]
	 */
	public function getPackets() : array
	{
		return $this->packets;
	}

	public function getTimestamp() : float
	{
		return $this->timestamp;
	}
}
