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

use function count;

/**
 * Sets the message shown on the death screen underneath "You died!"
 */
class DeathInfoPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::DEATH_INFO_PACKET;

	private string $messageTranslationKey;
	/** @var string[] */
	private array $messageParameters;

	/**
	 * @generate-create-func
	 * @param string[] $messageParameters
	 */
	public static function create(string $messageTranslationKey, array $messageParameters) : self
	{
		$result = new self();
		$result->messageTranslationKey = $messageTranslationKey;
		$result->messageParameters = $messageParameters;
		return $result;
	}

	public function getMessageTranslationKey() : string
	{
		return $this->messageTranslationKey;
	}

	/** @return string[] */
	public function getMessageParameters() : array
	{
		return $this->messageParameters;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->messageTranslationKey = $in->getString();

		$this->messageParameters = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; $i++) {
			$this->messageParameters[] = $in->getString();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->messageTranslationKey);

		$out->putUnsignedVarInt(count($this->messageParameters));
		foreach ($this->messageParameters as $parameter) {
			$out->putString($parameter);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleDeathInfo($this);
	}
}
