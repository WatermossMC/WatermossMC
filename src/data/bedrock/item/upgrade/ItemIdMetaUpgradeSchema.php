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

namespace watermossmc\data\bedrock\item\upgrade;

use function mb_strtolower;

final class ItemIdMetaUpgradeSchema
{
	/**
	 * @param string[]   $renamedIds
	 * @param string[][] $remappedMetas
	 * @phpstan-param array<string, string> $renamedIds
	 * @phpstan-param array<string, array<int, string>> $remappedMetas
	 */
	public function __construct(
		private array $renamedIds,
		private array $remappedMetas,
		private int $schemaId
	) {
	}

	public function getSchemaId() : int
	{
		return $this->schemaId;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getRenamedIds() : array
	{
		return $this->renamedIds;
	}

	/**
	 * @return string[][]
	 * @phpstan-return array<string, array<int, string>>
	 */
	public function getRemappedMetas() : array
	{
		return $this->remappedMetas;
	}

	public function renameId(string $id) : ?string
	{
		return $this->renamedIds[mb_strtolower($id, 'US-ASCII')] ?? null;
	}

	public function remapMeta(string $id, int $meta) : ?string
	{
		return $this->remappedMetas[mb_strtolower($id, 'US-ASCII')][$meta] ?? null;
	}
}
