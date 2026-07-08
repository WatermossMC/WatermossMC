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

final class ItemEntity extends Entity
{
    private string $itemTypeId;

    private int $count = 1;

    public function __construct(int $runtimeId, string $uuid, World $world, string $itemTypeId, int $count = 1)
    {
        parent::__construct($runtimeId, $uuid, $world);
        $this->itemTypeId = $itemTypeId;
        $this->count = $count;

        // Item entities usually have a specific visual look in EntityData
        $this->getEntityData()->set(1, EntityData::TYPE_INT, 0); // Example: Item visual state
    }

    public function getItemTypeId(): string
    {
        return $this->itemTypeId;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
