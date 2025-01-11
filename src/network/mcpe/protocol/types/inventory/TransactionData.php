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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\utils\BinaryDataException;

use function count;

abstract class TransactionData
{
	/** @var NetworkInventoryAction[] */
	protected array $actions = [];

	/**
	 * @return NetworkInventoryAction[]
	 */
	final public function getActions() : array
	{
		return $this->actions;
	}

	abstract public function getTypeId() : int;

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	final public function decode(PacketSerializer $stream) : void
	{
		$actionCount = $stream->getUnsignedVarInt();
		for ($i = 0; $i < $actionCount; ++$i) {
			$this->actions[] = (new NetworkInventoryAction())->read($stream);
		}
		$this->decodeData($stream);
	}

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	abstract protected function decodeData(PacketSerializer $stream) : void;

	final public function encode(PacketSerializer $stream) : void
	{
		$stream->putUnsignedVarInt(count($this->actions));
		foreach ($this->actions as $action) {
			$action->write($stream);
		}
		$this->encodeData($stream);
	}

	abstract protected function encodeData(PacketSerializer $stream) : void;
}
