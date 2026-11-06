<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use watermossmc\network\nethernet\DiscoveryCodec;
use watermossmc\network\nethernet\MessageReassembler;
use watermossmc\network\nethernet\MessageSegmenter;
use watermossmc\network\nethernet\Signal;
use watermossmc\network\nethernet\Uint64;
use watermossmc\network\nethernet\webrtc\Crc32c;
use watermossmc\network\nethernet\webrtc\SctpAssociation;
use watermossmc\network\nethernet\webrtc\Sdp;
use watermossmc\network\nethernet\webrtc\Stun;

class NetherNetTest extends TestCase
{
    public function testSegmentationRoundTrip(): void
    {
        $msg = random_bytes(600000);
        $segments = MessageSegmenter::split($msg);
        $this->assertCount(3, $segments);
        $this->assertSame(2, \ord($segments[0][0]));
        $this->assertSame(0, \ord($segments[2][0]));
        $this->assertSame(262144, \strlen($segments[0]));
        $this->assertSame([], MessageSegmenter::split(''));

        $r = new MessageReassembler();
        $this->assertNull($r->push($segments[0]));
        $this->assertNull($r->push($segments[1]));
        $this->assertSame($msg, $r->push($segments[2]));
    }

    public function testSmallMessageIsSingleSegment(): void
    {
        $segments = MessageSegmenter::split("\xfehello");
        $this->assertCount(1, $segments);
        $this->assertSame("\x00\xfehello", $segments[0]);
    }

    public function testSignalCodec(): void
    {
        $s = Signal::decode('CONNECTREQUEST 18446744073709551615 v=0' . "\r\n" . 'a=b');
        $this->assertNotNull($s);
        $this->assertSame(Signal::CONNECT_REQUEST, $s->type);
        $this->assertSame('18446744073709551615', $s->connectionId);
        $this->assertSame("v=0\r\na=b", $s->data);
        $this->assertSame('CONNECTERROR 7 3', (new Signal(Signal::CONNECT_ERROR, '7', '3'))->encode());
        $this->assertNull(Signal::decode('GARBAGE 1 x'));
    }

    public function testUint64(): void
    {
        foreach (['0', '1', '255', '256', '9223372036854775808', '18446744073709551615'] as $v) {
            $this->assertSame($v, Uint64::fromLe(Uint64::toLe($v)));
        }
    }

    public function testDiscoveryRoundTrip(): void
    {
        $body = DiscoveryCodec::encodeMessageBody('12345678901234567890', 'CONNECTERROR 1 7');
        $raw = DiscoveryCodec::encode(DiscoveryCodec::PACKET_MESSAGE, '42', $body);
        $pk = DiscoveryCodec::decode($raw);
        $this->assertNotNull($pk);
        $this->assertSame(DiscoveryCodec::PACKET_MESSAGE, $pk['id']);
        $this->assertSame('42', $pk['sender']);
        $msg = DiscoveryCodec::decodeMessageBody($pk['body']);
        $this->assertSame(['recipient' => '12345678901234567890', 'signal' => 'CONNECTERROR 1 7'], $msg);

        $raw[40] = $raw[40] ^ "\x01";
        $this->assertNull(DiscoveryCodec::decode($raw));
    }

    public function testCrc32cCheckValue(): void
    {
        $this->assertSame(0xE3069283, Crc32c::compute('123456789'));
    }

    public function testStunIntegrity(): void
    {
        $key = 'serverpassword0123456789';
        $txid = random_bytes(12);
        $resp = Stun::buildSuccess($txid, '192.168.1.10', 54321, $key);
        $this->assertNotNull($resp);
        $parsed = Stun::parse($resp);
        $this->assertNotNull($parsed);
        $this->assertSame(Stun::BINDING_SUCCESS, $parsed['type']);
        $this->assertTrue(Stun::verifyIntegrity($resp, $parsed, $key));
        $this->assertFalse(Stun::verifyIntegrity($resp, $parsed, 'wrong'));
    }

    public function testSdpOfferAnswer(): void
    {
        $offer = "v=0\r\no=- 1 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\nm=application 9 UDP/DTLS/SCTP webrtc-datachannel\r\n"
            . "a=ice-ufrag:abcd\r\na=ice-pwd:0123456789abcdef012345\r\na=fingerprint:sha-256 AA:BB:CC\r\na=setup:actpass\r\na=mid:0\r\na=sctp-port:5000\r\n";
        $o = Sdp::parseOffer($offer);
        $this->assertSame(['ufrag' => 'abcd', 'pwd' => '0123456789abcdef012345', 'fingerprint' => 'aabbcc', 'mid' => '0', 'sctpPort' => 5000], $o);
        $answer = Sdp::buildAnswer('0', 'u', 'p', 'AA:BB', 'IDENT', '10.0.0.2', 19134);
        $this->assertStringContainsString("a=identity:IDENT\r\nm=application", $answer);
        $this->assertStringNotContainsString('candidate', Sdp::buildAnswer('0', 'u', 'p', 'AA:BB', 'IDENT'));
        $this->assertStringContainsString('a=setup:passive', $answer);
        $this->assertStringNotContainsString('a=setup:actpass', $answer);
        $this->assertStringContainsString('a=ice-lite', $answer);
        $this->assertStringContainsString('10.0.0.2 19134 typ host', $answer);
    }

    public function testSctpUnreliableChannel(): void
    {
        $sentPackets = [];
        $sctp = new SctpAssociation(
            static function (string $pkt) use (&$sentPackets): void {
                $sentPackets[] = $pkt;
            },
            static function (int $sid, int $ppid, string $data): void {},
            static function (int $sid, string $label): void {},
            static function (string $why): void {},
            5000,
            5000
        );

        $this->assertFalse($sctp->isChannelUnreliable(1));
    }

    public function testMessageReassemblerFragmentationValidation(): void
    {
        $assembler = new MessageReassembler(false);
        try {
            $assembler->push("\x01aa");
            $this->fail('Fragmented segment on a non-fragmenting channel must throw');
        } catch (\watermossmc\network\nethernet\NetherNetException) {
            // expected
        }

        $reliableAssembler = new MessageReassembler(true);
        $this->assertNull($reliableAssembler->push("\x01part1"));
        $this->assertSame("part1part2", $reliableAssembler->push("\x00part2"));
    }

    public function testMessageReassemblerRejectsBadCountdown(): void
    {
        $r = new MessageReassembler(true);
        $this->assertNull($r->push("\x02a"));
        $this->expectException(\watermossmc\network\nethernet\NetherNetException::class);
        $r->push("\x00b");
    }

    public function testRateLimiterWindowAndPerKey(): void
    {
        $l = new \watermossmc\network\nethernet\RateLimiter(2, 1.0);
        $this->assertSame(0, $l->check('1.1.1.1', 0.0));
        $this->assertSame(0, $l->check('1.1.1.1', 0.1));
        $this->assertSame(1, $l->check('1.1.1.1', 0.2));
        $this->assertSame(2, $l->check('1.1.1.1', 0.3));
        $this->assertSame(0, $l->check('2.2.2.2', 0.3));
        $this->assertSame(0, $l->check('1.1.1.1', 1.5));

        $off = new \watermossmc\network\nethernet\RateLimiter(0, 1.0);
        $this->assertSame(0, $off->check('x', 0.0));
    }
}
