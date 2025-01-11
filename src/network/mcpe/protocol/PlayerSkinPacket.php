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

use Ramsey\Uuid\UuidInterface;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\skin\SkinData;

class PlayerSkinPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	public UuidInterface $uuid;
	public string $oldSkinName = "";
	public string $newSkinName = "";
	public SkinData $skin;

	/**
	 * @generate-create-func
	 */
	public static function create(UuidInterface $uuid, string $oldSkinName, string $newSkinName, SkinData $skin) : self
	{
		$result = new self();
		$result->uuid = $uuid;
		$result->oldSkinName = $oldSkinName;
		$result->newSkinName = $newSkinName;
		$result->skin = $skin;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->uuid = $in->getUUID();
		$this->skin = $in->getSkin();
		$this->newSkinName = $in->getString();
		$this->oldSkinName = $in->getString();
		$this->skin->setVerified($in->getBool());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUUID($this->uuid);
		$out->putSkin($this->skin);
		$out->putString($this->newSkinName);
		$out->putString($this->oldSkinName);
		$out->putBool($this->skin->isVerified());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerSkin($this);
	}
}
