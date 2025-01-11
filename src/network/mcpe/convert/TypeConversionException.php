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

namespace watermossmc\network\mcpe\convert;

/**
 * Thrown by TypeConverter if a problem occurred during converting of network types to PM core types (e.g. invalid item
 * ID, invalid NBT, etc).
 */
final class TypeConversionException extends \RuntimeException
{
	public static function wrap(\Throwable $previous, ?string $prefix = null) : self
	{
		return new self(($prefix !== null ? $prefix . ": " : "") . $previous->getMessage(), 0, $previous);
	}
}
