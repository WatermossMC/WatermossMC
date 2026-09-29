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

namespace watermossmc\crafting;

/**
 * A single crafting ingredient requirement: either a specific item identifier or a tag that
 * matches any item registered under that tag (e.g. "minecraft:coals").
 */
final class RecipeIngredient
{
    /**
     * @param list<string> $tagMembers empty unless this ingredient is a tag
     */
    public function __construct(
        public readonly ?string $identifier,
        public readonly ?string $tag,
        public readonly array $tagMembers,
        public readonly int $count
    ) {}

    public function accepts(string $identifier): bool
    {
        if ($this->identifier !== null) {
            return $this->identifier === $identifier;
        }
        return \in_array($identifier, $this->tagMembers, true);
    }
}
