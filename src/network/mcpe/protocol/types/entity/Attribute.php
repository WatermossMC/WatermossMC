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

final class Attribute
{
	/**
	 * @param AttributeModifier[] $modifiers
	 */
	public function __construct(
		private string $id,
		private float $min,
		private float $max,
		private float $current,
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
}
