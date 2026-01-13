<?php

declare(strict_types=1);

namespace WatermossMC\Network;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\PacketHandler;
use WatermossMC\Util\Logger;
use WatermossMC\Util\Motd;

final class RakNet
{
    public const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
    public const CONNECTED_PING = 0x00;
    public const CONNECTED_PONG = 0x03;
    public const UNCONNECTED_PING = 0x01;
    public const UNCONNECTED_PONG = 0x1C;
    public const OPEN_CONNECTION_REQUEST_1 = 0x05;
    public const OPEN_CONNECTION_REPLY_1 = 0x06;
    public const OPEN_CONNECTION_REQUEST_2 = 0x07;
    public const OPEN_CONNECTION_REPLY_2 = 0x08;
    public const CONNECTION_REQUEST = 0x09;
    public const CONNECTION_REQUEST_ACCEPTED = 0x10;
    public const NEW_INCOMING_CONNECTION = 0x13;
    public const DISCONNECT = 0x15;
    public const FRAME_SET_MIN = 0x80;
    public const FRAME_SET_MAX = 0x8D;
    public const ACK = 0xC0;
    public const NACK = 0xA0;

    /** @var Session[] */
    private static array $sessions = [];

    private static int $serverId;

    public static function init(): void
    {
        self::$serverId = random_int(1, \PHP_INT_MAX);
    }

    public static function handle(string $packet, string $addr, int $port, Socket $socket): void
    {
        if ($packet === '') {
            return;
        }

        $pid = \ord($packet[0]);

        Logger::debug(sprintf(
            "UDP recv len=%d first=0x%02X from %s:%d",
            \strlen($packet),
            \ord($packet[0]),
            $addr,
            $port
        ));

        if ($pid >= self::FRAME_SET_MIN && $pid <= self::FRAME_SET_MAX) {
            self::handleFrameSet($packet, $addr, $port, $socket);
            return;
        }

        match ($pid) {
            self::UNCONNECTED_PING => self::handlePing($packet, $addr, $port, $socket),
            self::OPEN_CONNECTION_REQUEST_1 => self::handleOpen1($packet, $addr, $port, $socket),
            self::OPEN_CONNECTION_REQUEST_2 => self::handleOpen2($packet, $addr, $port, $socket),
            self::CONNECTION_REQUEST => self::handleConnectionRequest($packet, $addr, $port, $socket),
            self::ACK => self::handleAck($packet, $addr, $port),
            self::NACK => self::handleNack($packet, $addr, $port, $socket),
            default => null
        };
    }

    private static function session(string $addr, int $port, ?\Socket $sock = null): Session
    {
        $key = "$addr:$port";

        if (!isset(self::$sessions[$key])) {
            $s = new Session($addr, $port);
            self::$sessions[$key] = $s;
        }

        $session = self::$sessions[$key];

        if ($sock !== null) {
            $session->attachSocket($sock);
        }

        return $session;
    }

    private static function readAddress(string $p, int &$o): void
    {
        $type = Binary::readByte($p, $o);

        if ($type === 4) {
            $o += 4;
            Binary::readShort($p, $o);
        } else {
            $o += 16;
            Binary::readShort($p, $o);
        }
    }

    private static function writeAddress(string $ip, int $port): string
    {
        $parts = explode('.', $ip);

        $buf = Binary::writeByte(4);
        foreach ($parts as $p) {
            $buf .= Binary::writeByte(((int)$p) ^ 0xFF);
        }
        $buf .= Binary::writeShort($port);

        return $buf;
    }

    private static function handlePing(string $p, string $a, int $po, Socket $s): void
    {
        $o = 1;
        $time = Binary::readLong($p, $o);
        if (substr($p, $o, 16) !== self::MAGIC) {
            return;
        }

        $buf = Binary::writeByte(self::UNCONNECTED_PONG);
        $buf .= Binary::writeLong($time);
        $buf .= Binary::writeLong(self::$serverId);
        $buf .= self::MAGIC;

        $motd = (new Motd())
            ->motd("WatermossMC")
            ->worldName("RakNet PHP Server")
            ->protocol(860)
            ->version("1.21.124")
            ->players(\count(self::$sessions), 20)
            ->gameMode("Survival", 1)
            ->port(19132)
            ->build(self::$serverId);

        $buf .= Binary::writeString($motd);
        socket_sendto($s, $buf, \strlen($buf), 0, $a, $po);
    }

    private static function handleOpen1(string $p, string $a, int $po, Socket $s): void
    {
        $o = 1;

        if (substr($p, $o, 16) !== self::MAGIC) {
            return;
        }
        $o += 16;

        $protocol = Binary::readByte($p, $o);
        $mtu = \strlen($p);

        Logger::debug("OPEN_CONNECTION_REQUEST_1 mtu={$mtu} protocol={$protocol}");

        $session = self::session($a, $po);
        $session->mtu = $mtu;
        $session->setRakNetState(Session::RN_CONNECTING);

        $buf = Binary::writeByte(self::OPEN_CONNECTION_REPLY_1);
        $buf .= self::MAGIC;
        $buf .= Binary::writeLong(self::$serverId);
        $buf .= Binary::writeByte(0);
        $buf .= Binary::writeShort($mtu);

        socket_sendto($s, $buf, \strlen($buf), 0, $a, $po);
    }

    private static function handleOpen2(string $p, string $a, int $po, Socket $s): void
    {
        $o = 1;

        if (substr($p, $o, 16) !== self::MAGIC) {
            return;
        }
        $o += 16;

        self::readAddress($p, $o);

        $mtu = Binary::readShort($p, $o);
        $clientGuid = Binary::readLong($p, $o);

        Logger::debug("OPEN_CONNECTION_REQUEST_2 mtu={$mtu} guid={$clientGuid}");

        $session = self::session($a, $po);
        $session->guid = $clientGuid;
        $session->mtu = $mtu;

        $buf = Binary::writeByte(self::OPEN_CONNECTION_REPLY_2);
        $buf .= self::MAGIC;
        $buf .= Binary::writeLong(self::$serverId);
        $buf .= self::writeAddress($a, $po);
        $buf .= Binary::writeShort($mtu);
        $buf .= Binary::writeByte(0);

        socket_sendto($s, $buf, \strlen($buf), 0, $a, $po);
    }

    private static function handleConnectionRequest(string $p, string $a, int $po, Socket $sock): void
    {
        Logger::debug("CONNECTION_REQUEST received");
        $o = 1;
        $clientGuid = Binary::readLong($p, $o);
        $time = Binary::readLong($p, $o);
        $useSecurity = Binary::readByte($p, $o);

        $session = self::session($a, $po);
        $session->guid = $clientGuid;

        $buf = Binary::writeByte(self::CONNECTION_REQUEST_ACCEPTED);
        $buf .= self::writeAddress($a, $po);
        $buf .= Binary::writeShort(0);

        for ($i = 0; $i < 10; $i++) {
            $buf .= self::writeAddress("255.255.255.255", 19132);
        }

        $buf .= Binary::writeLong($time);
        $buf .= Binary::writeLong(time());

        Logger::debug("CONNECTION_REQUEST_ACCEPTED packet");

        self::sendReliable(
            $session,
            $buf,
            Reliability::RELIABLE,
            $sock
        );

        $buf = Binary::writeByte(self::NEW_INCOMING_CONNECTION);
        $buf .= self::writeAddress($a, $po);

        for ($i = 0; $i < 20; $i++) {
            $buf .= self::writeAddress("255.255.255.255", 19132);
        }

        $buf .= Binary::writeLong((int)(microtime(true) * 1000));
        $buf .= Binary::writeLong((int)(microtime(true) * 1000));

        self::sendReliable(
            $session,
            $buf,
            Reliability::RELIABLE,
            $sock
        );

        Logger::debug("NEW_INCOMING_CONNECTION SENT");
        $session->setRakNetState(Session::RN_CONNECTED);
        $session->setMcpeState(Session::MC_NONE);

        self::flush($session, $sock);
    }

    private static function sendReliable(
        Session $s,
        string $payload,
        int $reliability,
        Socket $sock,
        int $channel = 0
    ): void {
        $frameSetSeq = $s->frameSeq++;
        $reliableSeq = $s->reliableSeq++;

        if (!isset($s->orderedSeq[$channel])) {
            $s->orderedSeq[$channel] = 0;
        }


        $buf = Binary::writeByte(self::FRAME_SET_MIN);
        $buf .= Binary::writeTriad($frameSetSeq);


        $flags = ($reliability << 5) & 0xE0;
        $buf .= Binary::writeByte($flags);
        $buf .= Binary::writeShort(\strlen($payload) * 8);
        $buf .= Binary::writeTriad($reliableSeq);


        if ($reliability === Reliability::RELIABLE_ORDERED) {
            $buf .= Binary::writeTriad($s->orderedSeq[$channel]++);
            $buf .= Binary::writeByte($channel);
        }

        $buf .= $payload;

        $s->reliableQueue[$reliableSeq] = $buf;

        $s->sendQueue[] = $buf;
    }

    private static function sendUnreliable(
        Session $s,
        string $payload,
        Socket $sock,
        int $channel = 0
    ): void {
        $frameSetSeq = $s->frameSeq++;

        $buf = Binary::writeByte(self::FRAME_SET_MIN);
        $buf .= Binary::writeTriad($frameSetSeq);

        $buf .= Binary::writeByte(0x00);
        $buf .= Binary::writeShort(\strlen($payload) * 8);

        $buf .= $payload;

        $s->sendQueue[] = $buf;
    }

    private static function handleFrameSet(string $p, string $a, int $po, Socket $sock): void
    {
        $session = self::session($a, $po);
        $o = 1;
        $len = \strlen($p);

        if ($o + 3 > $len) {
            return;
        }

        $seq = Binary::readTriad($p, $o);
        $session->markReceived($seq);

        Logger::debug("FrameSet recv seq=$seq from {$a}:{$po}");

        while (true) {

            if ($o + 3 > $len) {
                break;
            }

            $flags = \ord($p[$o++]);
            $reliability = $flags >> 5;
            $fragmented = ($flags & 0x10) !== 0;

            if ($o + 2 > $len) {
                break;
            }

            $lengthBits = Binary::readShort($p, $o);
            $frameLength = intdiv($lengthBits + 7, 8);


            if ($reliability !== 0) {
                if ($o + 3 > $len) {
                    break;
                }
                Binary::readTriad($p, $o);
            }


            if ($reliability === Reliability::RELIABLE_ORDERED) {
                if ($o + 4 > $len) {
                    break;
                }
                Binary::readTriad($p, $o);
                $o++;
            }

            $splitId = 0;
            $splitIndex = 0;
            $splitCount = 0;
            if ($fragmented) {
                if ($o + 10 > $len) {
                    break;
                }
                $splitCount = Binary::readInt($p, $o);
                $splitId = Binary::readShort($p, $o);
                $splitIndex = Binary::readInt($p, $o);
            }

            if ($o + $frameLength > $len) {
                break;
            }

            $body = substr($p, $o, $frameLength);
            $o += $frameLength;

            if ($fragmented) {
                if (isset($session->completedSplits[$splitId])) {
                    $body = null;
                } else {
                    if (!isset($session->splitQueue[$splitId])) {
                        $session->splitQueue[$splitId] = array_fill(0, $splitCount, null);
                    }

                    $session->splitQueue[$splitId][$splitIndex] = $body;

                    $receivedCount = \count(array_filter($session->splitQueue[$splitId], fn ($v) => $v !== null));

                    if ($receivedCount === $splitCount) {
                        $body = implode('', $session->splitQueue[$splitId]);
                        unset($session->splitQueue[$splitId]);

                        $session->completedSplits[$splitId] = true;
                        Logger::debug("Split Packet Reassembled! Total len=" . \strlen($body));
                    } else {
                        $body = null;
                    }
                }
            }

            if ($body === null || $body === '') {
                continue;
            }

            $pid = \ord($body[0]);

            Logger::debug(sprintf(
                "Connected frame PID=0x%02X len=%d reliability=%d",
                $pid,
                \strlen($body),
                $reliability
            ));

            if ($pid === self::CONNECTION_REQUEST) {
                self::handleConnectionRequest($body, $a, $po, $sock);
                continue;
            }

            if ($pid === self::NEW_INCOMING_CONNECTION) {
                Logger::debug("NEW_INCOMING_CONNECTION received (client)");
                $session->setRakNetState(Session::RN_CONNECTED);
                continue;
            }

            if ($pid === self::CONNECTED_PING) {
                $o2 = 1;
                $time = Binary::readLong($body, $o2);

                $pong = Binary::writeByte(self::CONNECTED_PONG);
                $pong .= Binary::writeLong($time);
                $pong .= Binary::writeLong((int)(microtime(true) * 1000));

                self::sendUnreliable($session, $pong, $sock);
                continue;
            }

            if ($pid === 0xFE) {
                if (!$session->hasSentNetworkSettings()) {
                    $batch = substr($body, 1);

                    Logger::debug(sprintf(
                        "MCPE batch PRE-NETWORK raw len=%d first=0x%02X",
                        \strlen($batch),
                        $batch !== '' ? \ord($batch[0]) : 0
                    ));

                    PacketHandler::handleBatch($batch, $session, $sock);
                    continue;
                }

                if (\strlen($body) < 2) {
                    Logger::debug("MCPE batch POST-NETWORK invalid (too short)");
                    continue;
                }

                $compressionId = \ord($body[1]);
                $payload = substr($body, 2);

                Logger::debug(sprintf(
                    "MCPE batch POST-NETWORK compression=0x%02X payloadLen=%d enc=%s",
                    $compressionId,
                    \strlen($payload),
                    $session->isEncryptionEnabled() ? 'yes' : 'no'
                ));

                if ($session->isEncryptionEnabled()) {
                    try {
                        $payload = $session->decrypt($payload);
                    } catch (\Throwable $e) {
                        Logger::error("Decryption failed: " . $e->getMessage());
                    }
                }

                switch ($compressionId) {
                    case 0x00: // ZLIB (RAW DEFLATE)
                        Logger::debug(
                            "ZLIB payload head=" . bin2hex(substr($payload, 0, 8))
                        );

                        $batch = gzinflate($payload);

                        if ($batch === false) {
                            Logger::debug("MCPE batch ZLIB decode failed");
                            break;
                        }

                        Logger::debug(sprintf(
                            "MCPE batch ZLIB inflated len=%d first=0x%02X",
                            \strlen($batch),
                            $batch !== '' ? \ord($batch[0]) : 0
                        ));
                        break;

                    case 0xFF: // NO COMPRESSION
                        $batch = $payload;
                        break;

                    default:
                        Logger::debug(sprintf(
                            "MCPE batch unknown compressionId=0x%02X",
                            $compressionId
                        ));
                        continue 2;
                }

                if ($batch === false) {
                    Logger::debug("MCPE batch decompress failed");
                    continue;
                }

                Logger::debug(sprintf(
                    "MCPE batch decoded len=%d first=0x%02X",
                    \strlen($batch),
                    $batch !== '' ? \ord($batch[0]) : 0
                ));

                PacketHandler::handleBatch($batch, $session, $sock);
                continue;
            }
        }

        self::sendAck($seq, $a, $po, $sock);
        Logger::debug("ACK sent seq=$seq to $a:$po");
    }

    private static function sendAck(int $seq, string $a, int $p, Socket $s): void
    {
        $buf = Binary::writeByte(self::ACK);
        $buf .= Binary::writeShort(1);
        $buf .= Binary::writeByte(1);
        $buf .= Binary::writeTriad($seq);

        socket_sendto($s, $buf, \strlen($buf), 0, $a, $p);
    }

    private static function handleAck(string $p, string $a, int $po): void
    {
        $o = 1;
        $count = Binary::readShort($p, $o);
        $session = self::session($a, $po);

        for ($i = 0; $i < $count; $i++) {
            $isRange = \ord($p[$o++]);

            if ($isRange === 1) {
                $seq = Binary::readTriad($p, $o);
                self::handleAckSeq($session, $seq);
            } else {
                $start = Binary::readTriad($p, $o);
                $end = Binary::readTriad($p, $o);

                for ($seq = $start; $seq <= $end; $seq++) {
                    self::handleAckSeq($session, $seq);
                }
            }
        }
    }

    private static function handleAckSeq(Session $session, int $seq): void
    {
        unset($session->reliableQueue[$seq]);
    }

    private static function handleNack(string $p, string $a, int $po, Socket $sock): void
    {
        $o = 1;
        $count = Binary::readShort($p, $o);
        $session = self::session($a, $po);

        for ($i = 0; $i < $count; $i++) {
            $isSingle = \ord($p[$o++]);

            if ($isSingle === 1) {
                $seq = Binary::readTriad($p, $o);

                if (isset($session->reliableQueue[$seq])) {
                    socket_sendto(
                        $sock,
                        $session->reliableQueue[$seq],
                        \strlen($session->reliableQueue[$seq]),
                        0,
                        $a,
                        $po
                    );
                }
            } else {
                $start = Binary::readTriad($p, $o);
                $end = Binary::readTriad($p, $o);

                for ($s = $start; $s <= $end; $s++) {
                    if (isset($session->reliableQueue[$s])) {
                        socket_sendto(
                            $sock,
                            $session->reliableQueue[$s],
                            \strlen($session->reliableQueue[$s]),
                            0,
                            $a,
                            $po
                        );
                    }
                }
            }
        }
    }

    public static function flush(Session $session, Socket $sock): void
    {
        if (!isset($session->sendQueue) || empty($session->sendQueue)) {
            return;
        }

        foreach ($session->sendQueue as $buffer) {
            socket_sendto(
                $sock,
                $buffer,
                \strlen($buffer),
                0,
                $session->address,
                $session->port
            );
        }

        $session->sendQueue = [];
    }
}
