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

use watermossmc\utils\BinaryDataException;

abstract class Packet
{
	/** @var int */
	public static $ID = -1;

	public function encode(PacketSerializer $out) : void
	{
		$this->encodeHeader($out);
		$this->encodePayload($out);
	}

	protected function encodeHeader(PacketSerializer $out) : void
	{
		$out->putByte(static::$ID);
	}

	abstract protected function encodePayload(PacketSerializer $out) : void;

	/**
	 * @throws BinaryDataException
	 */
	public function decode(PacketSerializer $in) : void
	{
		$this->decodeHeader($in);
		$this->decodePayload($in);
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function decodeHeader(PacketSerializer $in) : void
	{
		$in->getByte(); //PID
	}

	/**
	 * @throws BinaryDataException
	 */
	abstract protected function decodePayload(PacketSerializer $in) : void;
}
