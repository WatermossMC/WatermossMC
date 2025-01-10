<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\types\entity;

/**
 * Affects which parameter of the target attribute is modified.
 */
final class AttributeModifierTargetOperand
{
	private function __construct()
	{
		//NOOP
	}

	public const MIN = 0;
	public const MAX = 1;
	public const CURRENT = 2;
}
