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

class PacketHandlingException extends \RuntimeException
{
	/** @phpstan-var DisconnectReason::* */
	private int $disconnectReason;

	/**
	 * @phpstan-param DisconnectReason::* $disconnectReason
	 */
	public function __construct(string $message, int $disconnectReason, int $code = 0, ?\Throwable $previous = null)
	{
		$this->disconnectReason = $disconnectReason;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @phpstan-return DisconnectReason::*
	 */
	public function getDisconnectReason() : int
	{
		return $this->disconnectReason;
	}
}
