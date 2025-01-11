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

namespace watermossmc\network\raklib\protocol;

final class SplitPacketInfo
{
	public function __construct(
		private int $id,
		private int $partIndex,
		private int $totalPartCount
	) {
		//TODO: argument validation
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getPartIndex() : int
	{
		return $this->partIndex;
	}

	public function getTotalPartCount() : int
	{
		return $this->totalPartCount;
	}
}
