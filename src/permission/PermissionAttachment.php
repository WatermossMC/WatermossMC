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

use watermossmc\plugin\Plugin;
use watermossmc\plugin\PluginException;
use watermossmc\utils\Utils;

use function spl_object_id;

class PermissionAttachment
{
	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private array $permissions = [];

	/**
	 * @var PermissibleInternal[]
	 * @phpstan-var array<int, PermissibleInternal>
	 */
	private array $subscribers = [];

	/**
	 * @throws PluginException
	 */
	public function __construct(
		private Plugin $plugin
	) {
		if (!$plugin->isEnabled()) {
			throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
		}
	}

	public function getPlugin() : Plugin
	{
		return $this->plugin;
	}

	/**
	 * @return PermissibleInternal[]
	 * @phpstan-return array<int, PermissibleInternal>
	 */
	public function getSubscribers() : array
	{
		return $this->subscribers;
	}

	/**
	 * @return bool[]
	 * @phpstan-return array<string, bool>
	 */
	public function getPermissions() : array
	{
		return $this->permissions;
	}

	private function recalculatePermissibles() : void
	{
		foreach ($this->subscribers as $permissible) {
			$permissible->recalculatePermissions();
		}
	}

	public function clearPermissions() : void
	{
		$this->permissions = [];
		$this->recalculatePermissibles();
	}

	/**
	 * @param bool[] $permissions
	 * @phpstan-param array<string, bool> $permissions
	 */
	public function setPermissions(array $permissions) : void
	{
		foreach (Utils::stringifyKeys($permissions) as $key => $value) {
			$this->permissions[$key] = $value;
		}
		$this->recalculatePermissibles();
	}

	/**
	 * @param string[] $permissions
	 */
	public function unsetPermissions(array $permissions) : void
	{
		foreach ($permissions as $node) {
			unset($this->permissions[$node]);
		}
		$this->recalculatePermissibles();
	}

	public function setPermission(Permission|string $name, bool $value) : void
	{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if (isset($this->permissions[$name])) {
			if ($this->permissions[$name] === $value) {
				return;
			}
			/* Because of the way child permissions are calculated, permissions which were set later in time are
			 * preferred over earlier ones when conflicts in inherited permission values occur.
			 * Here's the kicker: This behaviour depends on PHP's internal array ordering, which maintains insertion
			 * order -- BUT -- assigning to an existing index replaces the old value WITHOUT changing the order.
			 * (what crazy person thought relying on this this was a good idea?!?!?!?!?!)
			 *
			 * This removes the old value so that the new value will be added at the end of the array's internal order
			 * instead of directly taking the place of the older value.
			 */
			unset($this->permissions[$name]);
		}
		$this->permissions[$name] = $value;
		$this->recalculatePermissibles();
	}

	public function unsetPermission(Permission|string $name) : void
	{
		$name = $name instanceof Permission ? $name->getName() : $name;
		if (isset($this->permissions[$name])) {
			unset($this->permissions[$name]);
			$this->recalculatePermissibles();
		}
	}

	/**
	 * @internal
	 */
	public function subscribePermissible(PermissibleInternal $permissible) : void
	{
		$this->subscribers[spl_object_id($permissible)] = $permissible;
	}

	/**
	 * @internal
	 */
	public function unsubscribePermissible(PermissibleInternal $permissible) : void
	{
		unset($this->subscribers[spl_object_id($permissible)]);
	}
}
