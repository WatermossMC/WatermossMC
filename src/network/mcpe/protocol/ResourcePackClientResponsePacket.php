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

class ResourcePackClientResponsePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET;

	public const STATUS_REFUSED = 1;
	public const STATUS_SEND_PACKS = 2;
	public const STATUS_HAVE_ALL_PACKS = 3;
	public const STATUS_COMPLETED = 4;

	public int $status;
	/** @var string[] */
	public array $packIds = [];

	/**
	 * @generate-create-func
	 * @param string[] $packIds
	 */
	public static function create(int $status, array $packIds) : self
	{
		$result = new self();
		$result->status = $status;
		$result->packIds = $packIds;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->status = $in->getByte();
		$entryCount = $in->getLShort();
		$this->packIds = [];
		while ($entryCount-- > 0) {
			$this->packIds[] = $in->getString();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->status);
		$out->putLShort(count($this->packIds));
		foreach ($this->packIds as $id) {
			$out->putString($id);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePackClientResponse($this);
	}
}
