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

namespace watermossmc\item;

use watermossmc\utils\Limits;
use watermossmc\utils\Utils;

use function sprintf;
use function strlen;

class WritableBookPage
{
	public const PAGE_LENGTH_HARD_LIMIT_BYTES = Limits::INT16_MAX;
	public const PHOTO_NAME_LENGTH_HARD_LIMIT_BYTES = Limits::INT16_MAX;

	private string $text;
	private string $photoName;

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function checkLength(string $string, string $name, int $maxLength) : void
	{
		if (strlen($string) > $maxLength) {
			throw new \InvalidArgumentException(sprintf("$name must be at most %d bytes, but have %d bytes", $maxLength, strlen($string)));
		}
	}

	public function __construct(string $text, string $photoName = "")
	{
		self::checkLength($text, "Text", self::PAGE_LENGTH_HARD_LIMIT_BYTES);
		self::checkLength($photoName, "Photo name", self::PHOTO_NAME_LENGTH_HARD_LIMIT_BYTES);
		Utils::checkUTF8($text);
		$this->text = $text;
		$this->photoName = $photoName;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function getPhotoName() : string
	{
		return $this->photoName;
	}
}
