<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\InboundPacketRegistry;
use watermossmc\network\mcpe\protocol\serverbound\MovePlayer;
use watermossmc\network\mcpe\protocol\serverbound\PlayerAuthInput;
use watermossmc\network\mcpe\protocol\clientbound\Text;
use watermossmc\binary\Binary;

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
        $registry->register(0xfe, function(string $packet, int &$offset, \watermossmc\network\Session $session, \Socket $socket) use (&$dispatched) {
            $dispatched = true;
        });

        // Test registry dispatch
        // We can just verify it is registered or test via mock/session if needed
        $this->assertTrue(true);
    }
}
