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

class SetTitlePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_TITLE_PACKET;

	public const TYPE_CLEAR_TITLE = 0;
	public const TYPE_RESET_TITLE = 1;
	public const TYPE_SET_TITLE = 2;
	public const TYPE_SET_SUBTITLE = 3;
	public const TYPE_SET_ACTIONBAR_MESSAGE = 4;
	public const TYPE_SET_ANIMATION_TIMES = 5;
	public const TYPE_SET_TITLE_JSON = 6;
	public const TYPE_SET_SUBTITLE_JSON = 7;
	public const TYPE_SET_ACTIONBAR_MESSAGE_JSON = 8;

	public int $type;
	public string $text = "";
	public int $fadeInTime = 0;
	public int $stayTime = 0;
	public int $fadeOutTime = 0;
	public string $xuid = "";
	public string $platformOnlineId = "";
	public string $filteredTitleText = "";

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $type,
		string $text,
		int $fadeInTime,
		int $stayTime,
		int $fadeOutTime,
		string $xuid,
		string $platformOnlineId,
		string $filteredTitleText,
	) : self {
		$result = new self();
		$result->type = $type;
		$result->text = $text;
		$result->fadeInTime = $fadeInTime;
		$result->stayTime = $stayTime;
		$result->fadeOutTime = $fadeOutTime;
		$result->xuid = $xuid;
		$result->platformOnlineId = $platformOnlineId;
		$result->filteredTitleText = $filteredTitleText;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->type = $in->getVarInt();
		$this->text = $in->getString();
		$this->fadeInTime = $in->getVarInt();
		$this->stayTime = $in->getVarInt();
		$this->fadeOutTime = $in->getVarInt();
		$this->xuid = $in->getString();
		$this->platformOnlineId = $in->getString();
		$this->filteredTitleText = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->type);
		$out->putString($this->text);
		$out->putVarInt($this->fadeInTime);
		$out->putVarInt($this->stayTime);
		$out->putVarInt($this->fadeOutTime);
		$out->putString($this->xuid);
		$out->putString($this->platformOnlineId);
		$out->putString($this->filteredTitleText);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetTitle($this);
	}

	private static function type(int $type) : self
	{
		$result = new self();
		$result->type = $type;
		return $result;
	}

	private static function text(int $type, string $text) : self
	{
		$result = self::type($type);
		$result->text = $text;
		return $result;
	}

	public static function title(string $text) : self
	{
		return self::text(self::TYPE_SET_TITLE, $text);
	}

	public static function subtitle(string $text) : self
	{
		return self::text(self::TYPE_SET_SUBTITLE, $text);
	}

	public static function actionBarMessage(string $text) : self
	{
		return self::text(self::TYPE_SET_ACTIONBAR_MESSAGE, $text);
	}

	public static function clearTitle() : self
	{
		return self::type(self::TYPE_CLEAR_TITLE);
	}

	public static function resetTitleOptions() : self
	{
		return self::type(self::TYPE_RESET_TITLE);
	}

	public static function setAnimationTimes(int $fadeIn, int $stay, int $fadeOut) : self
	{
		$result = self::type(self::TYPE_SET_ANIMATION_TIMES);
		$result->fadeInTime = $fadeIn;
		$result->stayTime = $stay;
		$result->fadeOutTime = $fadeOut;
		return $result;
	}
}
