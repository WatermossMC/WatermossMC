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

namespace watermossmc\command\defaults;

use watermossmc\command\Command;
use watermossmc\command\CommandSender;
use watermossmc\command\utils\InvalidCommandSyntaxException;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\player\Player;

use function array_shift;
use function count;
use function implode;
use function inet_pton;

class BanIpCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"ban-ip",
			KnownTranslationFactory::watermossmc_command_ban_ip_description(),
			KnownTranslationFactory::commands_banip_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_BAN_IP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			throw new InvalidCommandSyntaxException();
		}

		$value = array_shift($args);
		$reason = implode(" ", $args);

		if (inet_pton($value) !== false) {
			$this->processIPBan($value, $sender, $reason);

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success($value));
		} else {
			if (($player = $sender->getServer()->getPlayerByPrefix($value)) instanceof Player) {
				$ip = $player->getNetworkSession()->getIp();
				$this->processIPBan($ip, $sender, $reason);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success_players($ip, $player->getName()));
			} else {
				$sender->sendMessage(KnownTranslationFactory::commands_banip_invalid());

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason) : void
	{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach ($sender->getServer()->getOnlinePlayers() as $player) {
			if ($player->getNetworkSession()->getIp() === $ip) {
				$player->kick(KnownTranslationFactory::watermossmc_disconnect_ban($reason !== "" ? $reason : KnownTranslationFactory::watermossmc_disconnect_ban_ip()));
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
