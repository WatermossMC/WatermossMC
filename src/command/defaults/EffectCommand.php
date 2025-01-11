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

use watermossmc\command\CommandSender;
use watermossmc\command\utils\InvalidCommandSyntaxException;
use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\effect\StringToEffectParser;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\utils\Limits;
use watermossmc\utils\TextFormat;

use function count;
use function strtolower;

class EffectCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"effect",
			KnownTranslationFactory::watermossmc_command_effect_description(),
			KnownTranslationFactory::commands_effect_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_EFFECT_SELF,
			DefaultPermissionNames::COMMAND_EFFECT_OTHER
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0], DefaultPermissionNames::COMMAND_EFFECT_SELF, DefaultPermissionNames::COMMAND_EFFECT_OTHER);
		if ($player === null) {
			return true;
		}
		$effectManager = $player->getEffects();

		if (strtolower($args[1]) === "clear") {
			$effectManager->clear();

			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed_all($player->getDisplayName()));
			return true;
		}

		$effect = StringToEffectParser::getInstance()->parse($args[1]);
		if ($effect === null) {
			$sender->sendMessage(KnownTranslationFactory::commands_effect_notFound($args[1])->prefix(TextFormat::RED));
			return true;
		}

		$amplification = 0;

		if (count($args) >= 3) {
			if (($d = $this->getBoundedInt($sender, $args[2], 0, (int) (Limits::INT32_MAX / 20))) === null) {
				return false;
			}
			$duration = $d * 20; //ticks
		} else {
			$duration = null;
		}

		if (count($args) >= 4) {
			$amplification = $this->getBoundedInt($sender, $args[3], 0, 255);
			if ($amplification === null) {
				return false;
			}
		}

		$visible = true;
		if (count($args) >= 5) {
			$v = strtolower($args[4]);
			if ($v === "on" || $v === "true" || $v === "t" || $v === "1") {
				$visible = false;
			}
		}

		if ($duration === 0) {
			if (!$effectManager->has($effect)) {
				if (count($effectManager->all()) === 0) {
					$sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive_all($player->getDisplayName()));
				} else {
					$sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive($effect->getName(), $player->getDisplayName()));
				}
				return true;
			}

			$effectManager->remove($effect);
			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed($effect->getName(), $player->getDisplayName()));
		} else {
			$instance = new EffectInstance($effect, $duration, $amplification, $visible);
			$effectManager->add($instance);
			self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success($effect->getName(), (string) $instance->getAmplifier(), $player->getDisplayName(), (string) ($instance->getDuration() / 20)));
		}

		return true;
	}
}
