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

class CodeBuilderPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CODE_BUILDER_PACKET;

	private string $url;
	private bool $openCodeBuilder;

	/**
	 * @generate-create-func
	 */
	public static function create(string $url, bool $openCodeBuilder) : self
	{
		$result = new self();
		$result->url = $url;
		$result->openCodeBuilder = $openCodeBuilder;
		return $result;
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function openCodeBuilder() : bool
	{
		return $this->openCodeBuilder;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->url = $in->getString();
		$this->openCodeBuilder = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->url);
		$out->putBool($this->openCodeBuilder);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCodeBuilder($this);
	}
}
