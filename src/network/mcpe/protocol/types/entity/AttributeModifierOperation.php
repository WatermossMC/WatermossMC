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

/**
 * Affects how the modifier value is applied to the target attribute. These operations are described on the Minecraft
 * Wiki at https://minecraft.fandom.com/wiki/Attribute
 *
 * These operations are listed in the order that they are applied in.
 */
final class AttributeModifierOperation
{
	private function __construct()
	{
		//NOOP
	}

	/** Adds the modifier value to the attribute's base value. */
	public const ADD = 0;
	/**
	 * Multiplies the value by (1 + x), where x is the sum of all MULTIPLY_BASE modifiers' amounts. Multiple modifiers
	 * of this type have additive effects on each other.
	 */
	public const MULTIPLY_BASE = 1;
	/**
	 * Each modifier of this type multiplies the value by (1 + x), where x is the current modifier's value. Multiple
	 * modifiers of this type have multiplicative effects on each other.
	 */
	public const MULTIPLY_TOTAL = 2;
	/**
	 * Limits the result value. If the result value is greater than the limit, it is set to the limit.
	 */
	public const CAP = 3;
}
