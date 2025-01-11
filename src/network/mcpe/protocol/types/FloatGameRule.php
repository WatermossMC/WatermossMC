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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class FloatGameRule extends GameRule
{
	use GetTypeIdFromConstTrait;

	public const ID = GameRuleType::FLOAT;

	private float $value;

	public function __construct(float $value, bool $isPlayerModifiable)
	{
		parent::__construct($isPlayerModifiable);
		$this->value = $value;
	}

	public function getValue() : float
	{
		return $this->value;
	}

	public function encode(PacketSerializer $out) : void
	{
		$out->putLFloat($this->value);
	}

	public static function decode(PacketSerializer $in, bool $isPlayerModifiable) : self
	{
		return new self($in->getLFloat(), $isPlayerModifiable);
	}
}
