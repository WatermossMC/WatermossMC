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

namespace watermossmc\network\mcpe\protocol\types\camera;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

final class CameraAimAssistCategories
{
	/**
	 * @param CameraAimAssistCategory[] $categories
	 */
	public function __construct(
		private string $identifier,
		private array $categories
	) {
	}

	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	/**
	 * @return CameraAimAssistCategory[]
	 */
	public function getCategories() : array
	{
		return $this->categories;
	}

	public static function read(PacketSerializer $in) : self
	{
		$identifier = $in->getString();

		$categories = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$categories[] = CameraAimAssistCategory::read($in);
		}

		return new self(
			$identifier,
			$categories
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->identifier);
		$out->putUnsignedVarInt(count($this->categories));
		foreach ($this->categories as $category) {
			$category->write($out);
		}
	}
}
