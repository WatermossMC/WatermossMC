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

/**
 * Displays a toast notification on the client's screen (usually a little box at the top, like the one shown when
 * getting an Xbox Live achievement).
 */
class ToastRequestPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::TOAST_REQUEST_PACKET;

	private string $title;
	private string $body;

	/**
	 * @generate-create-func
	 */
	public static function create(string $title, string $body) : self
	{
		$result = new self();
		$result->title = $title;
		$result->body = $body;
		return $result;
	}

	public function getTitle() : string
	{
		return $this->title;
	}

	public function getBody() : string
	{
		return $this->body;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->title = $in->getString();
		$this->body = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->title);
		$out->putString($this->body);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleToastRequest($this);
	}
}
