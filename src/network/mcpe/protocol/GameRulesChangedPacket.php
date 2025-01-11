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
use watermossmc\network\mcpe\protocol\types\GameRule;

class GameRulesChangedPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::GAME_RULES_CHANGED_PACKET;

	/**
	 * @var GameRule[]
	 * @phpstan-var array<string, GameRule>
	 */
	public array $gameRules = [];

	/**
	 * @generate-create-func
	 * @param GameRule[] $gameRules
	 * @phpstan-param array<string, GameRule> $gameRules
	 */
	public static function create(array $gameRules) : self
	{
		$result = new self();
		$result->gameRules = $gameRules;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->gameRules = $in->getGameRules();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putGameRules($this->gameRules);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleGameRulesChanged($this);
	}
}
