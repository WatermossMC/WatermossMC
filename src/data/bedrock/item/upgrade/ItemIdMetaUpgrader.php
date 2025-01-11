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

use function ksort;

use const SORT_NUMERIC;

/**
 * Upgrades old item string IDs and metas to newer ones according to the given schemas.
 */
final class ItemIdMetaUpgrader
{
	/**
	 * @var ItemIdMetaUpgradeSchema[]
	 * @phpstan-var array<int, ItemIdMetaUpgradeSchema>
	 */
	private array $idMetaUpgradeSchemas = [];

	/**
	 * @param ItemIdMetaUpgradeSchema[] $idMetaUpgradeSchemas
	 * @phpstan-param array<int, ItemIdMetaUpgradeSchema> $idMetaUpgradeSchemas
	 */
	public function __construct(
		array $idMetaUpgradeSchemas,
	) {
		foreach ($idMetaUpgradeSchemas as $schema) {
			$this->addSchema($schema);
		}
	}

	public function addSchema(ItemIdMetaUpgradeSchema $schema) : void
	{
		if (isset($this->idMetaUpgradeSchemas[$schema->getSchemaId()])) {
			throw new \InvalidArgumentException("Already have a schema with priority " . $schema->getSchemaId());
		}
		$this->idMetaUpgradeSchemas[$schema->getSchemaId()] = $schema;
		ksort($this->idMetaUpgradeSchemas, SORT_NUMERIC);
	}

	/**
	 * @return ItemIdMetaUpgradeSchema[]
	 * @phpstan-return array<int, ItemIdMetaUpgradeSchema>
	 */
	public function getSchemas() : array
	{
		return $this->idMetaUpgradeSchemas;
	}

	/**
	 * @phpstan-return array{string, int}
	 */
	public function upgrade(string $id, int $meta) : array
	{
		$newId = $id;
		$newMeta = $meta;
		foreach ($this->idMetaUpgradeSchemas as $schema) {
			if (($remappedMetaId = $schema->remapMeta($newId, $newMeta)) !== null) {
				$newId = $remappedMetaId;
				$newMeta = 0;
			} elseif (($renamedId = $schema->renameId($newId)) !== null) {
				$newId = $renamedId;
			}
		}

		return [$newId, $newMeta];
	}
}
