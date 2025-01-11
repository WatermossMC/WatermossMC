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

use Symfony\Component\Filesystem\Path;
use watermossmc\command\CommandSender;
use watermossmc\lang\KnownTranslationFactory;
use watermossmc\permission\DefaultPermissionNames;

use function date;

class DumpMemoryCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"dumpmemory",
			KnownTranslationFactory::watermossmc_command_dumpmemory_description(),
			"/dumpmemory [path]"
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_DUMPMEMORY);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		$sender->getServer()->getMemoryManager()->dumpServerMemory($args[0] ?? (Path::join($sender->getServer()->getDataPath(), "memory_dumps", date("D_M_j-H.i.s-T_Y"))), 48, 80);
		return true;
	}
}
