<?php

declare(strict_types=1);

require_once __DIR__ . '/Binary.php';

final class RakNet
{
    public const UNCONNECTED_PING = 0x01;
    public const UNCONNECTED_PONG = 0x1C;
    public const OPEN_CONNECTION_REQUEST_1 = 0x05;
    public const OPEN_CONNECTION_REPLY_1 = 0x06;
    public const OPEN_CONNECTION_REQUEST_2 = 0x07;
    public const OPEN_CONNECTION_REPLY_2 = 0x08;
    public const FRAME_SET_MIN = 0x80;
    public const FRAME_SET_MAX = 0x8F;
    public const ACK = 0xC0;
    public const NACK = 0xA0;
    public const MC_PLAY_STATUS = 0x02;
    public const LOGIN_SUCCESS = 0;
    public const LOGIN_FAILED_CLIENT = 1;
    public const LOGIN_FAILED_SERVER = 2;
    public const MC_RESOURCE_PACKS_INFO = 0x06;
    public const MC_RESOURCE_PACK_STACK = 0x07;
    public const MC_RESOURCE_PACK_CLIENT_RESPONSE = 0x08;
    public const MC_START_GAME = 0x0B;
    public const MC_LEVEL_CHUNK = 0x3A;
    public const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

    private static array $sessions = [];

    private static $socket;

    public static function handle(string $packet, string $address, int $port, $socket): void
    {
        if ($packet === '') {
            return;
        }

        self::$socket = $socket;

        $pid = \ord($packet[0]);

        if ($pid >= self::FRAME_SET_MIN && $pid <= self::FRAME_SET_MAX) {
            self::handleFrameSet($packet, $address, $port, $socket);
            return;
        }

        switch ($pid) {
            case self::UNCONNECTED_PING:
                self::handlePing($packet, $address, $port, $socket);
                break;

            case self::OPEN_CONNECTION_REQUEST_1:
                self::handleOpenConnection1($packet, $address, $port, $socket);
                break;

            case self::OPEN_CONNECTION_REQUEST_2:
                self::handleOpenConnection2($packet, $address, $port, $socket);
                break;
        }
    }

    private static function handlePing(string $packet, string $address, int $port, $socket): void
    {
        $offset = 1;

        $pingTime = Binary::readLong($packet, $offset);
        $magic = substr($packet, $offset, 16);

        if ($magic !== self::MAGIC) {
            return;
        }

        self::sendPong($pingTime, $address, $port, $socket);
    }

    private static function handleOpenConnection1(
        string $packet,
        string $address,
        int $port,
        $socket
    ): void {
        $offset = 1;

        $magic = substr($packet, $offset, 16);
        $offset += 16;

        if ($magic !== self::MAGIC) {
            return;
        }

        $protocol = Binary::readByte($packet, $offset);

        $buffer = Binary::writeByte(self::OPEN_CONNECTION_REPLY_1);
        $buffer .= self::MAGIC;
        $buffer .= Binary::writeLong(self::getServerId());
        $buffer .= Binary::writeByte(0); // no security
        $buffer .= Binary::writeShort(1492); // MTU

        socket_sendto($socket, $buffer, \strlen($buffer), 0, $address, $port);
    }

    private static function handleOpenConnection2(
        string $packet,
        string $address,
        int $port,
        $socket
    ): void {
        $offset = 1;

        $magic = substr($packet, $offset, 16);
        $offset += 16;

        if ($magic !== self::MAGIC) {
            return;
        }

        $offset += 7;

        $mtu = Binary::readShort($packet, $offset);
        $clientId = Binary::readLong($packet, $offset);

        $buffer = Binary::writeByte(self::OPEN_CONNECTION_REPLY_2);
        $buffer .= self::MAGIC;
        $buffer .= Binary::writeLong(self::getServerId());
        $buffer .= Binary::writeShort($mtu);
        $buffer .= Binary::writeByte(0); // no security

        socket_sendto($socket, $buffer, \strlen($buffer), 0, $address, $port);
    }

    private static function handleFrameSet(
        string $packet,
        string $address,
        int $port,
        $socket
    ): void {
        $key = "$address:$port";

        if (!isset(self::$sessions[$key])) {
            self::$sessions[$key] = [
                'sequence' => 0,
            ];
        }

        $flags = \ord($packet[0]);

        $offset = 1;
        $sequence = Binary::readTriad($packet, $offset);

        $frameFlags = \ord($packet[$offset++]);

        $hasLength = ($frameFlags & 0x10) !== 0;
        $length = $hasLength
            ? Binary::readShort($packet, $offset)
            : \strlen($packet) - $offset;

        $payload = substr($packet, $offset, $length);

        self::handleEncapsulated($payload, $address, $port);

        self::$sessions[$key]['sequence'] = $sequence;

        self::sendAck($sequence, $address, $port, $socket);
    }

    private static function handleEncapsulated(
        string $payload,
        string $address,
        int $port
    ): void {
        $pid = \ord($payload[0]);

        if ($pid !== 0xFE) {
            return;
        }

        $compressed = substr($payload, 1);
        $data = zlib_decode($compressed);

        $offset = 0;
        while ($offset < \strlen($data)) {
            $len = Binary::readInt($data, $offset);
            $packet = substr($data, $offset, $len);
            $offset += $len;

            self::handleMinecraftPacket($packet, $address, $port);
        }
    }

    private static function handleMinecraftPacket(
        string $packet,
        string $address,
        int $port
    ): void {
        $pid = \ord($packet[0]);

        switch ($pid) {
            case 0x01:
                self::handleLogin($packet, $address, $port);
                break;

            case self::MC_RESOURCE_PACK_CLIENT_RESPONSE:
                self::handleResourcePackResponse($packet, $address, $port);
                break;

            case 0x13:
                self::handleMove($packet, $address, $port);
                break;
        }
    }

    /**
     * MINECRAFT PACKETS (NOT COMPLETED)
    */
    private static function handleLogin(
        string $packet,
        string $address,
        int $port
    ): void {
        $offset = 1;

        $protocol = Binary::readInt($packet, $offset);
        $chainJson = Binary::readStringInt($packet, $offset);
        $clientJwt = Binary::readStringInt($packet, $offset);

        echo "LOGIN from $address:$port\n";
        echo "Protocol: $protocol\n";

        [$h, $payload, $sig] = explode('.', $clientJwt);
        $clientData = json_decode(base64_decode(strtr($payload, '-_', '+/'), true), true);

        echo "Username: " . ($clientData['ThirdPartyName'] ?? 'Unknown') . \PHP_EOL;
        self::sendPlayStatus(
            self::LOGIN_SUCCESS,
            $address,
            $port,
            self::$socket
        );
        self::sendResourcePacksInfo($address, $port, self::$socket);
    }

    private static function handleResourcePackResponse(
        string $packet,
        string $address,
        int $port
    ): void {
        $offset = 1;
        $status = Binary::readByte($packet, $offset);

        // 0 = NONE
        // 1 = REFUSED
        // 2 = SEND_PACKS
        // 3 = HAVE_ALL_PACKS
        // 4 = COMPLETED

        if ($status === 0 || $status === 3) {
            self::sendResourcePackStack($address, $port, self::$socket);
        }

        if ($status === 4 || $status === 3) {
            self::sendStartGame($address, $port, self::$socket);
            //self::sendSetTime($address, $port, self::$socket);
            self::sendChunk($address, $port, self::$socket);
        }
    }

    private static function handleMove(
        string $packet,
        string $address,
        int $port
    ): void {
        // TERIMA AJA (anti rubberband)
    }

    private static function sendPlayStatus(
        int $status,
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(self::MC_PLAY_STATUS);
        $payload .= Binary::writeInt($status);

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendResourcePacksInfo(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(self::MC_RESOURCE_PACKS_INFO);
        $payload .= Binary::writeByte(0); // mustAccept = false
        $payload .= Binary::writeShort(0); // behavior packs
        $payload .= Binary::writeShort(0); // resource packs

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendResourcePackStack(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(self::MC_RESOURCE_PACK_STACK);
        $payload .= Binary::writeByte(0); // mustAccept
        $payload .= Binary::writeShort(0); // behavior packs
        $payload .= Binary::writeShort(0); // resource packs
        $payload .= Binary::writeByte(0); // experimental

        self::sendMinecraftPacket($payload, $address, $port, $socket);
        self::sendStartGame($address, $port, self::$socket);
        self::sendTime($address, $port, self::$socket);
        self::sendSpawnPosition($address, $port, self::$socket);
        self::sendPlayerList($address, $port, self::$socket);
        self::sendAddPlayer($address, $port, self::$socket);
        self::sendChunk($address, $port, self::$socket);
    }

    private static function sendStartGame(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(self::MC_START_GAME);

        // === Entity IDs ===
        $payload .= Binary::writeLong(1); // entityId
        $payload .= Binary::writeLong(1); // runtimeEntityId

        // === Player position ===
        $payload .= pack("g", 0.0); // x
        $payload .= pack("g", 100.0); // y
        $payload .= pack("g", 0.0); // z

        // === Rotation ===
        $payload .= pack("g", 0.0); // pitch
        $payload .= pack("g", 0.0); // yaw

        // === World seed ===
        $payload .= Binary::writeInt(0);

        // === Game mode ===
        $payload .= Binary::writeInt(1); // creative

        // === Difficulty ===
        $payload .= Binary::writeInt(1);

        // === Spawn position ===
        $payload .= Binary::writeInt(0);
        $payload .= Binary::writeInt(100);
        $payload .= Binary::writeInt(0);

        // === World rules (MINIMAL) ===
        $payload .= Binary::writeBool(false); // achievements disabled
        $payload .= Binary::writeInt(0); // time
        $payload .= Binary::writeBool(false); // edu edition
        $payload .= Binary::writeBool(false); // rain
        $payload .= Binary::writeBool(false); // lightning

        // === World name ===
        $payload .= Binary::writeString("PHP World");

        // === Gamerules count ===
        $payload .= Binary::writeInt(0);

        // === Experiments ===
        $payload .= Binary::writeInt(0);
        $payload .= Binary::writeBool(false);

        // === Level ID & world name ===
        $payload .= Binary::writeString("php_level");
        $payload .= Binary::writeString("PHP World");

        // === Premium / multiplayer flags ===
        $payload .= Binary::writeBool(false); // premium
        $payload .= Binary::writeBool(true);  // multiplayer
        $payload .= Binary::writeBool(true);  // broadcast

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendSpawnPosition(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(0x44);
        $payload .= Binary::writeInt(0);   // type
        $payload .= Binary::writeInt(0);   // x
        $payload .= Binary::writeInt(64);  // y
        $payload .= Binary::writeInt(0);   // z

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendTime(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(0x0A);
        $payload .= Binary::writeInt(6000); // siang

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendPlayerList(
        string $address,
        int $port,
        $socket
    ): void {
        $uuid = random_bytes(16);

        $payload = Binary::writeByte(0x3F);
        $payload .= Binary::writeByte(0); // ADD
        $payload .= Binary::writeInt(1);  // count
        $payload .= $uuid;
        $payload .= Binary::writeLong(1); // entity id
        $payload .= Binary::writeStringInt("Player");
        $payload .= Binary::writeStringInt(""); // skin

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendAddPlayer(
        string $address,
        int $port,
        $socket
    ): void {
        $uuid = random_bytes(16);

        $payload = Binary::writeByte(0x0C);
        $payload .= $uuid;
        $payload .= Binary::writeStringInt("Player");
        $payload .= Binary::writeLong(1); // entityId
        $payload .= Binary::writeLong(1); // runtimeId

        $payload .= pack("g", 0.0);
        $payload .= pack("g", 64.0);
        $payload .= pack("g", 0.0);

        $payload .= pack("g", 0.0);
        $payload .= pack("g", 0.0);
        $payload .= pack("g", 0.0);

        $payload .= Binary::writeInt(0); // held item
        $payload .= Binary::writeInt(0); // metadata count

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendChunk(
        string $address,
        int $port,
        $socket
    ): void {
        $payload = Binary::writeByte(self::MC_LEVEL_CHUNK);

        $payload .= Binary::writeInt(0); // chunkX
        $payload .= Binary::writeInt(0); // chunkZ

        $payload .= Binary::writeByte(1); // full chunk
        $payload .= Binary::writeShort(1); // subchunk count

        // ===== SUBCHUNK =====
        // version
        $subchunk = Binary::writeByte(8); // subchunk version
        $subchunk .= Binary::writeByte(1); // layers

        // block storage (flat: stone)
        $palette = [
            ['name' => 'minecraft:stone', 'states' => [], 'version' => 17959425],
        ];

        $bits = 1;
        $subchunk .= Binary::writeByte($bits << 1);

        // block data (4096 blocks)
        $subchunk .= str_repeat("\x00", 512);

        // palette
        $subchunk .= Binary::writeInt(\count($palette));
        foreach ($palette as $p) {
            $subchunk .= Binary::writeStringInt(json_encode($p));
        }

        // biome data
        $subchunk .= str_repeat("\x00", 256);

        $payload .= Binary::writeInt(\strlen($subchunk));
        $payload .= $subchunk;

        self::sendMinecraftPacket($payload, $address, $port, $socket);
    }

    private static function sendFrame(
        string $payload,
        string $address,
        int $port,
        $socket
    ): void {
        $key = "$address:$port";

        if (!isset(self::$sessions[$key])) {
            return;
        }

        $sequence = ++self::$sessions[$key]['sequence'];

        $buffer = Binary::writeByte(0x80); // frame flags
        $buffer .= Binary::writeTriad($sequence);

        // encapsulated frame
        $buffer .= Binary::writeByte(0x10); // reliability + length flag
        $buffer .= Binary::writeShort(\strlen($payload));
        $buffer .= $payload;

        socket_sendto($socket, $buffer, \strlen($buffer), 0, $address, $port);
    }

    private static function sendMinecraftPacket(
        string $payload,
        string $address,
        int $port,
        $socket
    ): void {
        // BATCH
        $batch = '';
        $batch .= Binary::writeInt(\strlen($payload));
        $batch .= $payload;

        $compressed = zlib_encode($batch, \ZLIB_ENCODING_DEFLATE);

        $packet = Binary::writeByte(0xFE);
        $packet .= $compressed;

        self::sendFrame($packet, $address, $port, $socket);
    }

    private static function sendAck(
        int $sequence,
        string $address,
        int $port,
        $socket
    ): void {
        $buffer = Binary::writeByte(self::ACK);
        $buffer .= Binary::writeShort(1); // 1 record
        $buffer .= Binary::writeTriad($sequence);
        $buffer .= Binary::writeTriad($sequence);

        socket_sendto($socket, $buffer, \strlen($buffer), 0, $address, $port);
    }

    private static function sendPong(int $pingTime, string $address, int $port, $socket): void
    {
        $serverId = 123456789;

        $motd = self::buildMotd($serverId);

        $buffer = Binary::writeByte(self::UNCONNECTED_PONG);
        $buffer .= Binary::writeLong($pingTime);
        $buffer .= Binary::writeLong($serverId);
        $buffer .= self::MAGIC;
        $buffer .= Binary::writeString($motd);

        socket_sendto($socket, $buffer, \strlen($buffer), 0, $address, $port);
    }

    private static function getServerId(): int
    {
        static $id = null;
        if ($id === null) {
            $id = random_int(1, \PHP_INT_MAX);
        }
        return $id;
    }

    private static function buildMotd(int $serverId): string
    {
        return implode(";", [
            "MCPE",
            "WatermossMC",
            "860",
            "1.21.123",
            "0",
            "20",
            (string)$serverId,
            "Minimal PHP RakNet Server",
            "Survival",
            "1",
            "19132",
        ]);
    }
}
