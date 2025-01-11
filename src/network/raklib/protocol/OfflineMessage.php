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
use watermossmc\utils\BinaryStream;

abstract class OfflineMessage extends Packet
{
	/**
	 * Magic bytes used to distinguish offline messages from loose garbage.
	 */
	private const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

	protected string $magic = self::MAGIC;

	/**
	 * @return void
	 * @throws BinaryDataException
	 */
	protected function readMagic(BinaryStream $in)
	{
		$this->magic = $in->get(16);
	}

	/**
	 * @return void
	 */
	protected function writeMagic(BinaryStream $out)
	{
		$out->put($this->magic);
	}

	public function isValid() : bool
	{
		return $this->magic === self::MAGIC;
	}
}
