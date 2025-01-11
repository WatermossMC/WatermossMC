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

namespace watermossmc\scheduler;

final class BulkCurlTaskOperation
{
	/**
	 * @param string[] $extraHeaders
	 * @param mixed[]  $extraOpts
	 * @phpstan-param list<string> $extraHeaders
	 * @phpstan-param array<int, mixed> $extraOpts
	 */
	public function __construct(
		private string $page,
		private float $timeout = 10,
		private array $extraHeaders = [],
		private array $extraOpts = []
	) {
	}

	public function getPage() : string
	{
		return $this->page;
	}

	public function getTimeout() : float
	{
		return $this->timeout;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getExtraHeaders() : array
	{
		return $this->extraHeaders;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<int, mixed>
	 */
	public function getExtraOpts() : array
	{
		return $this->extraOpts;
	}
}
