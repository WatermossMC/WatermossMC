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

namespace watermossmc\network\mcpe\protocol\types\recipe;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class MolangItemDescriptor implements ItemDescriptor
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::MOLANG;

	public function __construct(
		private string $molangExpression,
		private int $molangVersion
	) {
	}

	public function getMolangExpression() : string
	{
		return $this->molangExpression;
	}

	public function getMolangVersion() : int
	{
		return $this->molangVersion;
	}

	public static function read(PacketSerializer $in) : self
	{
		$expression = $in->getString();
		$version = $in->getByte();

		return new self($expression, $version);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->molangExpression);
		$out->putByte($this->molangVersion);
	}
}
