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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\binary\McpeBinary;

final class FullContainerName
{
    public function __construct(
        public readonly int $containerId,
        public readonly ?int $dynamicId = null
    ) {}

    public static function read(string $buffer, int &$offset): self
    {
        $containerId = McpeBinary::readUnsignedByte($buffer, $offset);
        $dynamicId = McpeBinary::readBool($buffer, $offset) ? McpeBinary::readUnsignedLInt($buffer, $offset) : null;
        return new self($containerId, $dynamicId);
    }

    public function write(): string
    {
        $out = McpeBinary::writeByte($this->containerId);
        if ($this->dynamicId !== null) {
            $out .= McpeBinary::writeBool(true);
            $out .= McpeBinary::writeUnsignedLInt($this->dynamicId);
        } else {
            $out .= McpeBinary::writeBool(false);
        }
        return $out;
    }
}
