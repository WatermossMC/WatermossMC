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

namespace watermossmc\command;

use watermossmc\command\utils\CommandException;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\lang\Translatable;
use watermossmc\permission\PermissionManager;
use watermossmc\Server;
use watermossmc\utils\BroadcastLoggerForwarder;
use watermossmc\utils\TextFormat;

use function explode;
use function implode;
use function str_replace;

abstract class Command
{
	private string $name;

	private string $nextLabel;
	private string $label;

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $aliases = [];

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $activeAliases = [];

	private ?CommandMap $commandMap = null;

	protected Translatable|string $description = "";

	protected Translatable|string $usageMessage;

	/** @var string[] */
	private array $permission = [];
	private ?string $permissionMessage = null;

	/**
	 * @param string[] $aliases
	 * @phpstan-param list<string> $aliases
	 */
	public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
	{
		$this->name = $name;
		$this->setLabel($name);
		$this->setDescription($description);
		$this->usageMessage = $usageMessage ?? ("/" . $name);
		$this->setAliases($aliases);
	}

	/**
	 * @param string[] $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return string[]
	 */
	public function getPermissions() : array
	{
		return $this->permission;
	}

	/**
	 * @param string[] $permissions
	 */
	public function setPermissions(array $permissions) : void
	{
		$permissionManager = PermissionManager::getInstance();
		foreach ($permissions as $perm) {
			if ($permissionManager->getPermission($perm) === null) {
				throw new \InvalidArgumentException("Cannot use non-existing permission \"$perm\"");
			}
		}
		$this->permission = $permissions;
	}

	public function setPermission(?string $permission) : void
	{
		$this->setPermissions($permission === null ? [] : explode(";", $permission));
	}

	public function testPermission(CommandSender $target, ?string $permission = null) : bool
	{
		if ($this->testPermissionSilent($target, $permission)) {
			return true;
		}

		if ($this->permissionMessage === null) {
			$target->sendMessage(KnownTranslationFactory::watermossmc_command_error_permission($this->name)->prefix(TextFormat::RED));
		} elseif ($this->permissionMessage !== "") {
			$target->sendMessage(str_replace("<permission>", $permission ?? implode(";", $this->permission), $this->permissionMessage));
		}

		return false;
	}

	public function testPermissionSilent(CommandSender $target, ?string $permission = null) : bool
	{
		$list = $permission !== null ? [$permission] : $this->permission;
		foreach ($list as $p) {
			if ($target->hasPermission($p)) {
				return true;
			}
		}

		return false;
	}

	public function getLabel() : string
	{
		return $this->label;
	}

	public function setLabel(string $name) : bool
	{
		$this->nextLabel = $name;
		if (!$this->isRegistered()) {
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 */
	public function register(CommandMap $commandMap) : bool
	{
		if ($this->allowChangesFrom($commandMap)) {
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	public function unregister(CommandMap $commandMap) : bool
	{
		if ($this->allowChangesFrom($commandMap)) {
			$this->commandMap = null;
			$this->activeAliases = $this->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	private function allowChangesFrom(CommandMap $commandMap) : bool
	{
		return $this->commandMap === null || $this->commandMap === $commandMap;
	}

	public function isRegistered() : bool
	{
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases() : array
	{
		return $this->activeAliases;
	}

	public function getPermissionMessage() : ?string
	{
		return $this->permissionMessage;
	}

	public function getDescription() : Translatable|string
	{
		return $this->description;
	}

	public function getUsage() : Translatable|string
	{
		return $this->usageMessage;
	}

	/**
	 * @param string[] $aliases
	 * @phpstan-param list<string> $aliases
	 */
	public function setAliases(array $aliases) : void
	{
		$this->aliases = $aliases;
		if (!$this->isRegistered()) {
			$this->activeAliases = $aliases;
		}
	}

	public function setDescription(Translatable|string $description) : void
	{
		$this->description = $description;
	}

	public function setPermissionMessage(string $permissionMessage) : void
	{
		$this->permissionMessage = $permissionMessage;
	}

	public function setUsage(Translatable|string $usage) : void
	{
		$this->usageMessage = $usage;
	}

	public static function broadcastCommandMessage(CommandSender $source, Translatable|string $message, bool $sendToSource = true) : void
	{
		$users = $source->getServer()->getBroadcastChannelSubscribers(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
		$result = KnownTranslationFactory::chat_type_admin($source->getName(), $message);
		$colored = $result->prefix(TextFormat::GRAY . TextFormat::ITALIC);

		if ($sendToSource) {
			$source->sendMessage($message);
		}

		foreach ($users as $user) {
			if ($user instanceof BroadcastLoggerForwarder) {
				$user->sendMessage($result);
			} elseif ($user !== $source) {
				$user->sendMessage($colored);
			}
		}
	}

	public function __toString() : string
	{
		return $this->name;
	}
}
