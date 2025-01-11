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
use watermossmc\network\mcpe\protocol\types\BlockPosition;

class LecternUpdatePacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LECTERN_UPDATE_PACKET;

	public int $page;
	public int $totalPages;
	public BlockPosition $blockPosition;

	/**
	 * @generate-create-func
	 */
	public static function create(int $page, int $totalPages, BlockPosition $blockPosition) : self
	{
		$result = new self();
		$result->page = $page;
		$result->totalPages = $totalPages;
		$result->blockPosition = $blockPosition;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->page = $in->getByte();
		$this->totalPages = $in->getByte();
		$this->blockPosition = $in->getBlockPosition();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->page);
		$out->putByte($this->totalPages);
		$out->putBlockPosition($this->blockPosition);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLecternUpdate($this);
	}
}
