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

class ModalFormResponsePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MODAL_FORM_RESPONSE_PACKET;

	public const CANCEL_REASON_CLOSED = 0;
	/** Sent if a form is sent when the player is on a loading screen */
	public const CANCEL_REASON_USER_BUSY = 1;

	public int $formId;
	public ?string $formData; //json
	public ?int $cancelReason;

	/**
	 * @generate-create-func
	 */
	private static function create(int $formId, ?string $formData, ?int $cancelReason) : self
	{
		$result = new self();
		$result->formId = $formId;
		$result->formData = $formData;
		$result->cancelReason = $cancelReason;
		return $result;
	}

	public static function response(int $formId, string $formData) : self
	{
		return self::create($formId, $formData, null);
	}

	public static function cancel(int $formId, int $cancelReason) : self
	{
		return self::create($formId, null, $cancelReason);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->formId = $in->getUnsignedVarInt();
		$this->formData = $in->readOptional($in->getString(...));
		$this->cancelReason = $in->readOptional($in->getByte(...));
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->formId);

		$out->writeOptional($this->formData, $out->putString(...));
		$out->writeOptional($this->cancelReason, $out->putByte(...));
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleModalFormResponse($this);
	}
}
