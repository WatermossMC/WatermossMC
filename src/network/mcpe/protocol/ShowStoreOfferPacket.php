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
use watermossmc\network\mcpe\protocol\types\ShowStoreOfferRedirectType;

class ShowStoreOfferPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SHOW_STORE_OFFER_PACKET;

	public string $offerId;
	public ShowStoreOfferRedirectType $redirectType;

	/**
	 * @generate-create-func
	 */
	public static function create(string $offerId, ShowStoreOfferRedirectType $redirectType) : self
	{
		$result = new self();
		$result->offerId = $offerId;
		$result->redirectType = $redirectType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->offerId = $in->getString();
		$this->redirectType = ShowStoreOfferRedirectType::fromPacket($in->getByte());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->offerId);
		$out->putByte($this->redirectType->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleShowStoreOffer($this);
	}
}
