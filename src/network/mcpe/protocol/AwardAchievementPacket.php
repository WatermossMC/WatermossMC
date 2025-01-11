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

class AwardAchievementPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::AWARD_ACHIEVEMENT_PACKET;

	private int $achievementId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $achievementId) : self
	{
		$result = new self();
		$result->achievementId = $achievementId;
		return $result;
	}

	public function getAchievementId() : int
	{
		return $this->achievementId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->achievementId = $in->getLInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLInt($this->achievementId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAwardAchievement($this);
	}
}
