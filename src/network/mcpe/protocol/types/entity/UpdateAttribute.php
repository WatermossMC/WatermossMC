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

namespace watermossmc\network\mcpe\protocol\types\entity;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

final class UpdateAttribute
{
	/**
	 * @param AttributeModifier[] $modifiers
	 */
	public function __construct(
		private string $id,
		private float $min,
		private float $max,
		private float $current,
		private float $defaultMin,
		private float $defaultMax,
		private float $default,
		private array $modifiers
	) {
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getMin() : float
	{
		return $this->min;
	}

	public function getMax() : float
	{
		return $this->max;
	}

	public function getCurrent() : float
	{
		return $this->current;
	}

	public function getDefaultMin() : float
	{
		return $this->defaultMin;
	}

	public function getDefaultMax() : float
	{
		return $this->defaultMax;
	}

	public function getDefault() : float
	{
		return $this->default;
	}

	/**
	 * @return AttributeModifier[]
	 */
	public function getModifiers() : array
	{
		return $this->modifiers;
	}

	public static function read(PacketSerializer $in) : self
	{
		$min = $in->getLFloat();
		$max = $in->getLFloat();
		$current = $in->getLFloat();
		$defaultMin = $in->getLFloat();
		$defaultMax = $in->getLFloat();
		$default = $in->getLFloat();
		$id = $in->getString();

		$modifiers = [];
		for ($j = 0, $modifierCount = $in->getUnsignedVarInt(); $j < $modifierCount; $j++) {
			$modifiers[] = AttributeModifier::read($in);
		}

		return new self($id, $min, $max, $current, $defaultMin, $defaultMax, $default, $modifiers);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->min);
		$out->putLFloat($this->max);
		$out->putLFloat($this->current);
		$out->putLFloat($this->defaultMin);
		$out->putLFloat($this->defaultMax);
		$out->putLFloat($this->default);
		$out->putString($this->id);

		$out->putUnsignedVarInt(count($this->modifiers));
		foreach ($this->modifiers as $modifier) {
			$modifier->write($out);
		}
	}
}
