<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\entity;

use watermossmc\world\World;

final class NPC extends LivingEntity
{
    public function __construct(int $runtimeId, string $uuid, World $world, string $name)
    {
        parent::__construct($runtimeId, $uuid, $world);
        $this->getEntityData()->set(19, EntityData::TYPE_STRING, $name);
    }

    public function tick(): void
    {
        parent::tick();

        // Simple AI: Randomly rotate a bit to look "alive"
        $this->yaw += (random_int(-100, 100) / 1000.0);
    }
}
