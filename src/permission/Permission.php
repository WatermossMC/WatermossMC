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

use watermossmc\lang\Translatable;

/**
 * Represents a permission
 */
class Permission
{
	private Translatable|string $description;

	/**
	 * Creates a new Permission object to be attached to Permissible objects
	 *
	 * @param bool[] $children
	 * @phpstan-param array<string, bool> $children
	 */
	public function __construct(
		private string $name,
		Translatable|string|null $description = null,
		private array $children = []
	) {
		$this->description = $description ?? ""; //TODO: wtf ????

		$this->recalculatePermissibles();
	}

	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return bool[]
	 * @phpstan-return array<string, bool>
	 */
	public function getChildren() : array
	{
		return $this->children;
	}

	public function getDescription() : Translatable|string
	{
		return $this->description;
	}

	public function setDescription(Translatable|string $value) : void
	{
		$this->description = $value;
	}

	/**
	 * @return PermissibleInternal[]
	 */
	public function getPermissibles() : array
	{
		return PermissionManager::getInstance()->getPermissionSubscriptions($this->name);
	}

	public function recalculatePermissibles() : void
	{
		$perms = $this->getPermissibles();

		foreach ($perms as $p) {
			$p->recalculatePermissions();
		}
	}

	public function addChild(string $name, bool $value) : void
	{
		$this->children[$name] = $value;
		$this->recalculatePermissibles();
	}

	public function removeChild(string $name) : void
	{
		unset($this->children[$name]);
		$this->recalculatePermissibles();

	}
}
