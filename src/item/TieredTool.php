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

abstract class TieredTool extends Tool
{
	protected ToolTier $tier;

	/**
	 * @param string[] $enchantmentTags
	 */
	public function __construct(ItemIdentifier $identifier, string $name, ToolTier $tier, array $enchantmentTags = [])
	{
		parent::__construct($identifier, $name, $enchantmentTags);
		$this->tier = $tier;
	}

	public function getMaxDurability() : int
	{
		return $this->tier->getMaxDurability();
	}

	public function getTier() : ToolTier
	{
		return $this->tier;
	}

	protected function getBaseMiningEfficiency() : float
	{
		return $this->tier->getBaseEfficiency();
	}

	public function getEnchantability() : int
	{
		return $this->tier->getEnchantability();
	}

	public function getFuelTime() : int
	{
		if ($this->tier === ToolTier::WOOD) {
			return 200;
		}

		return 0;
	}

	public function isFireProof() : bool
	{
		return $this->tier === ToolTier::NETHERITE;
	}
}
