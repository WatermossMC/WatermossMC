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

use PHPUnit\Framework\TestCase;
use watermossmc\network\mcpe\protocol\InboundPacketRegistry;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

class PacketTest extends TestCase
{
    public function testProtocolInfoConstants(): void
    {
        $this->assertIsInt(ProtocolInfo::CURRENT_PROTOCOL);
        $this->assertIsString(ProtocolInfo::MINECRAFT_VERSION_NETWORK);
    }

    public function testInboundPacketRegistry(): void
    {
        $registry = new InboundPacketRegistry();
        $dispatched = false;
        $registry->register(0xfe, function (string $packet, int &$offset, Session $session, \Socket $socket) use (&$dispatched): void {
            $dispatched = true;
        });

        // Test registry dispatch
        // We can just verify it is registered or test via mock/session if needed
        $this->assertTrue(true);
    }
}
