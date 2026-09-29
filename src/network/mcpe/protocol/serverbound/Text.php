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

namespace watermossmc\network\mcpe\protocol\serverbound;

use RuntimeException;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\clientbound\Text as ClientboundText;

final class Text
{
    public static function read(string $packet, int &$offset): array
    {
        $needsTranslation = McpeBinary::readBool($packet, $offset);
        $category = McpeBinary::readUnsignedByte($packet, $offset);
        $type = McpeBinary::readUnsignedByte($packet, $offset);

        $expected = ClientboundText::categoryOf($type);
        if ($category !== $expected) {
            throw new RuntimeException("Text packet type {$type} requires category {$expected}, got {$category}");
        }

        $sourceName = '';
        $parameters = [];
        switch ($expected) {
            case ClientboundText::CATEGORY_AUTHORED_MESSAGE:
                $sourceName = McpeBinary::readString($packet, $offset);
                $message = McpeBinary::readString($packet, $offset);
                break;
            case ClientboundText::CATEGORY_MESSAGE_WITH_PARAMETERS:
                $message = McpeBinary::readString($packet, $offset);
                $count = McpeBinary::readUnsignedVarInt($packet, $offset);
                if ($count > 64) {
                    throw new RuntimeException("Text packet has too many parameters: {$count}");
                }
                for ($i = 0; $i < $count; ++$i) {
                    $parameters[] = McpeBinary::readString($packet, $offset);
                }
                break;
            default:
                $message = McpeBinary::readString($packet, $offset);
                break;
        }

        $xboxUserId = McpeBinary::readString($packet, $offset);
        $platformChatId = McpeBinary::readString($packet, $offset);
        $filteredMessage = McpeBinary::readOptional(
            $packet,
            $offset,
            static fn (string $buffer, int &$o): string => McpeBinary::readString($buffer, $o)
        );

        return [
            'type' => $type,
            'needsTranslation' => $needsTranslation,
            'sourceName' => $sourceName,
            'message' => $message,
            'parameters' => $parameters,
            'xboxUserId' => $xboxUserId,
            'platformChatId' => $platformChatId,
            'filteredMessage' => $filteredMessage,
        ];
    }
}
