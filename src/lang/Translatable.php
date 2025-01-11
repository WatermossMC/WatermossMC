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

namespace watermossmc\lang;

use watermossmc\utils\Utils;

final class Translatable
{
	/** @var string[]|Translatable[] $params */
	protected array $params = [];

	/**
	 * @param (float|int|string|Translatable)[] $params
	 */
	public function __construct(
		protected string $text,
		array $params = []
	) {
		foreach (Utils::promoteKeys($params) as $k => $param) {
			if (!($param instanceof Translatable)) {
				$this->params[$k] = (string) $param;
			} else {
				$this->params[$k] = $param;
			}
		}
	}

	public function getText() : string
	{
		return $this->text;
	}

	/**
	 * @return string[]|Translatable[]
	 */
	public function getParameters() : array
	{
		return $this->params;
	}

	public function getParameter(int|string $i) : Translatable|string|null
	{
		return $this->params[$i] ?? null;
	}

	public function format(string $before, string $after) : self
	{
		return new self("$before%$this->text$after", $this->params);
	}

	public function prefix(string $prefix) : self
	{
		return new self("$prefix%$this->text", $this->params);
	}

	public function postfix(string $postfix) : self
	{
		return new self("%$this->text" . $postfix);
	}
}
