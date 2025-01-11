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
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;
use watermossmc\utils\TextFormat;

use function count;
use function memory_get_usage;
use function number_format;
use function round;

class GarbageCollectorCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"gc",
			KnownTranslationFactory::watermossmc_command_gc_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_GC);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$chunksCollected = 0;
		$entitiesCollected = 0;

		$memory = memory_get_usage();

		foreach ($sender->getServer()->getWorldManager()->getWorlds() as $world) {
			$diff = [count($world->getLoadedChunks()), count($world->getEntities())];
			$world->doChunkGarbageCollection();
			$world->unloadChunks(true);
			$chunksCollected += $diff[0] - count($world->getLoadedChunks());
			$entitiesCollected += $diff[1] - count($world->getEntities());
			$world->clearCache(true);
		}

		$cyclesCollected = $sender->getServer()->getMemoryManager()->triggerGarbageCollector();

		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gc_header()->format(TextFormat::GREEN . "---- " . TextFormat::RESET, TextFormat::GREEN . " ----" . TextFormat::RESET));
		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gc_chunks(TextFormat::RED . number_format($chunksCollected))->prefix(TextFormat::GOLD));
		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gc_entities(TextFormat::RED . number_format($entitiesCollected))->prefix(TextFormat::GOLD));

		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gc_cycles(TextFormat::RED . number_format($cyclesCollected))->prefix(TextFormat::GOLD));
		$sender->sendMessage(KnownTranslationFactory::watermossmc_command_gc_memoryFreed(TextFormat::RED . number_format(round((($memory - memory_get_usage()) / 1024) / 1024, 2), 2))->prefix(TextFormat::GOLD));
		return true;
	}
}
