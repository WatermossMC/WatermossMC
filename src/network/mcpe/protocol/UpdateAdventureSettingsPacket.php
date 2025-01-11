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
 * Updates "adventure settings". In vanilla, these flags apply to the whole world. This differs from abilities, which
 * apply only to the local player itself.
 * In practice, there's no difference between the two for a custom server.
 * This includes flags such as worldImmutable (makes players unable to build), autoJump, showNameTags, noPvM, and noMvP.
 */
class UpdateAdventureSettingsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_ADVENTURE_SETTINGS_PACKET;

	private bool $noAttackingMobs;
	private bool $noAttackingPlayers;
	private bool $worldImmutable;
	private bool $showNameTags;
	private bool $autoJump;

	/**
	 * @generate-create-func
	 */
	public static function create(bool $noAttackingMobs, bool $noAttackingPlayers, bool $worldImmutable, bool $showNameTags, bool $autoJump) : self
	{
		$result = new self();
		$result->noAttackingMobs = $noAttackingMobs;
		$result->noAttackingPlayers = $noAttackingPlayers;
		$result->worldImmutable = $worldImmutable;
		$result->showNameTags = $showNameTags;
		$result->autoJump = $autoJump;
		return $result;
	}

	public function isNoAttackingMobs() : bool
	{
		return $this->noAttackingMobs;
	}

	public function isNoAttackingPlayers() : bool
	{
		return $this->noAttackingPlayers;
	}

	public function isWorldImmutable() : bool
	{
		return $this->worldImmutable;
	}

	public function isShowNameTags() : bool
	{
		return $this->showNameTags;
	}

	public function isAutoJump() : bool
	{
		return $this->autoJump;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->noAttackingMobs = $in->getBool();
		$this->noAttackingPlayers = $in->getBool();
		$this->worldImmutable = $in->getBool();
		$this->showNameTags = $in->getBool();
		$this->autoJump = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->noAttackingMobs);
		$out->putBool($this->noAttackingPlayers);
		$out->putBool($this->worldImmutable);
		$out->putBool($this->showNameTags);
		$out->putBool($this->autoJump);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateAdventureSettings($this);
	}
}
