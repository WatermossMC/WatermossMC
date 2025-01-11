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

namespace watermossmc\permission;

use watermossmc\utils\Utils;

use function is_bool;
use function strtolower;

class PermissionParser
{
	public const DEFAULT_OP = "op";
	public const DEFAULT_NOT_OP = "notop";
	public const DEFAULT_TRUE = "true";
	public const DEFAULT_FALSE = "false";

	public const DEFAULT_STRING_MAP = [
		"op" => self::DEFAULT_OP,
		"isop" => self::DEFAULT_OP,
		"operator" => self::DEFAULT_OP,
		"isoperator" => self::DEFAULT_OP,
		"admin" => self::DEFAULT_OP,
		"isadmin" => self::DEFAULT_OP,

		"!op" => self::DEFAULT_NOT_OP,
		"notop" => self::DEFAULT_NOT_OP,
		"!operator" => self::DEFAULT_NOT_OP,
		"notoperator" => self::DEFAULT_NOT_OP,
		"!admin" => self::DEFAULT_NOT_OP,
		"notadmin" => self::DEFAULT_NOT_OP,

		"true" => self::DEFAULT_TRUE,
		"false" => self::DEFAULT_FALSE,
	];

	private const KEY_DEFAULT = "default";
	private const KEY_CHILDREN = "children";
	private const KEY_DESCRIPTION = "description";

	/**
	 * @throws PermissionParserException
	 */
	public static function defaultFromString(bool|string $value) : string
	{
		if (is_bool($value)) {
			return $value ? self::DEFAULT_TRUE : self::DEFAULT_FALSE;
		}
		$lower = strtolower($value);
		if (isset(self::DEFAULT_STRING_MAP[$lower])) {
			return self::DEFAULT_STRING_MAP[$lower];
		}

		throw new PermissionParserException("Unknown permission default name \"$value\"");
	}

	/**
	 * @param mixed[][] $data
	 * @phpstan-param array<string, array<string, mixed>> $data
	 *
	 * @return Permission[][]
	 * @phpstan-return array<string, list<Permission>>
	 * @throws PermissionParserException
	 */
	public static function loadPermissions(array $data, string $default = self::DEFAULT_FALSE) : array
	{
		$result = [];
		foreach (Utils::stringifyKeys($data) as $name => $entry) {
			$desc = null;
			if (isset($entry[self::KEY_DEFAULT])) {
				$default = PermissionParser::defaultFromString($entry[self::KEY_DEFAULT]);
			}

			if (isset($entry[self::KEY_CHILDREN])) {
				throw new PermissionParserException("Nested permission declarations are no longer supported. Declare each permission separately.");
			}

			if (isset($entry[self::KEY_DESCRIPTION])) {
				$desc = $entry[self::KEY_DESCRIPTION];
			}

			$result[$default][] = new Permission($name, $desc);
		}
		return $result;
	}
}
