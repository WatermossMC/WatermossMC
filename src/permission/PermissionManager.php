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

use watermossmc\Server;
use watermossmc\utils\Utils;

use function count;
use function spl_object_id;

class PermissionManager
{
	private static ?self $instance = null;

	public static function getInstance() : PermissionManager
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @var Permission[]
	 * @phpstan-var array<string, Permission>
	 */
	protected array $permissions = [];
	/**
	 * @var PermissibleInternal[][]
	 * @phpstan-var array<string, array<int, PermissibleInternal>>
	 */
	protected array $permSubs = [];

	public function getPermission(string $name) : ?Permission
	{
		return $this->permissions[$name] ?? null;
	}

	public function addPermission(Permission $permission) : bool
	{
		if (!isset($this->permissions[$permission->getName()])) {
			$this->permissions[$permission->getName()] = $permission;

			return true;
		}

		return false;
	}

	public function removePermission(Permission|string $permission) : void
	{
		if ($permission instanceof Permission) {
			unset($this->permissions[$permission->getName()]);
		} else {
			unset($this->permissions[$permission]);
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::subscribeToBroadcastChannel()
	 */
	public function subscribeToPermission(string $permission, PermissibleInternal $permissible) : void
	{
		if (!isset($this->permSubs[$permission])) {
			$this->permSubs[$permission] = [];
		}
		$this->permSubs[$permission][spl_object_id($permissible)] = $permissible;
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::unsubscribeFromBroadcastChannel()
	 */
	public function unsubscribeFromPermission(string $permission, PermissibleInternal $permissible) : void
	{
		if (isset($this->permSubs[$permission][spl_object_id($permissible)])) {
			if (count($this->permSubs[$permission]) === 1) {
				unset($this->permSubs[$permission]);
			} else {
				unset($this->permSubs[$permission][spl_object_id($permissible)]);
			}
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::unsubscribeFromAllBroadcastChannels()
	 */
	public function unsubscribeFromAllPermissions(PermissibleInternal $permissible) : void
	{
		foreach (Utils::promoteKeys($this->permSubs) as $permission => $subs) {
			if (count($subs) === 1 && isset($subs[spl_object_id($permissible)])) {
				unset($this->permSubs[$permission]);
			} else {
				unset($this->permSubs[$permission][spl_object_id($permissible)]);
			}
		}
	}

	/**
	 * @deprecated Superseded by server chat broadcast channels
	 * @see Server::getBroadcastChannelSubscribers()
	 * @return PermissibleInternal[]
	 */
	public function getPermissionSubscriptions(string $permission) : array
	{
		return $this->permSubs[$permission] ?? [];
	}

	/**
	 * @return Permission[]
	 */
	public function getPermissions() : array
	{
		return $this->permissions;
	}

	public function clearPermissions() : void
	{
		$this->permissions = [];
	}
}
