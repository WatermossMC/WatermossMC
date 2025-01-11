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
use watermossmc\network\mcpe\protocol\types\EnchantOption;

use function count;

class PlayerEnchantOptionsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_ENCHANT_OPTIONS_PACKET;

	/** @var EnchantOption[] */
	private array $options;

	/**
	 * @generate-create-func
	 * @param EnchantOption[] $options
	 */
	public static function create(array $options) : self
	{
		$result = new self();
		$result->options = $options;
		return $result;
	}

	/**
	 * @return EnchantOption[]
	 */
	public function getOptions() : array
	{
		return $this->options;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->options = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$this->options[] = EnchantOption::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->options));
		foreach ($this->options as $option) {
			$option->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerEnchantOptions($this);
	}
}
