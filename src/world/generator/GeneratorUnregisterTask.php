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

namespace watermossmc\world\generator;

use watermossmc\scheduler\AsyncTask;
use watermossmc\world\World;

class GeneratorUnregisterTask extends AsyncTask
{
	public int $worldId;

	public function __construct(World $world)
	{
		$this->worldId = $world->getId();
	}

	public function onRun() : void
	{
		ThreadLocalGeneratorContext::unregister($this->worldId);
	}
}
