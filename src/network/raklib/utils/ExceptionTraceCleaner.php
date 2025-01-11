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

namespace watermossmc\network\raklib\utils;

use function array_reverse;
use function count;
use function function_exists;
use function get_class;
use function gettype;
use function is_object;
use function method_exists;
use function str_replace;
use function strval;
use function substr;
use function xdebug_get_function_stack;

final class ExceptionTraceCleaner
{
	public function __construct(
		private string $mainPath
	) {
	}

	/**
	 * @param list<array<string, mixed>>|null $trace
	 *
	 * @return list<string>
	 */
	public function getTrace(int $start = 0, ?array $trace = null) : array
	{
		if ($trace === null) {
			if (function_exists("xdebug_get_function_stack") && count($trace = @xdebug_get_function_stack()) !== 0) {
				$trace = array_reverse($trace);
			} else {
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for ($i = $start; isset($trace[$i]); ++$i, ++$j) {
			$params = "";
			if (isset($trace[$i]["args"]) || isset($trace[$i]["params"])) {
				if (isset($trace[$i]["args"])) {
					$args = $trace[$i]["args"];
				} else {
					$args = $trace[$i]["params"];
				}
				foreach ($args as $name => $value) {
					$params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . @strval($value)) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? $this->cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" || $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . substr($params, 0, -2) . ")";
		}

		return $messages;
	}

	public function cleanPath(string $path) : string
	{
		return str_replace(["\\", ".php", "phar://", str_replace(["\\", "phar://"], ["/", ""], $this->mainPath)], ["/", "", "", ""], $path);
	}
}
