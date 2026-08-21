diff --git a/server.php b/server.php
index 5fbe76d..1150005 100644
--- a/server.php
+++ b/server.php
@@ -54,10 +54,7 @@ set_exception_handler(function (\Throwable $e) use (&$shutdown): void {
     }
 });
 
-<<<<<<< HEAD
 // Signal handlers for graceful shutdown (guarded if pcntl is available)
-=======
->>>>>>> 866a1c0 (...)
 if (\function_exists('pcntl_signal')) {
     pcntl_signal(SIGTERM, function () use (&$shutdown): void {
         Logger::info("Received SIGTERM, shutting down gracefully...");
@@ -72,10 +69,7 @@ if (\function_exists('pcntl_signal')) {
 
 Logger::info("Starting WatermossMC server on {$config['bind_ip']}:{$config['bind_port']}");
 
-<<<<<<< HEAD
 // Create UDP socket
-=======
->>>>>>> 866a1c0 (...)
 $socket = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
 if ($socket === false) {
     $error = socket_strerror(socket_last_error());
@@ -123,10 +117,7 @@ $tickInterval = 1_000_000_000 / 20; // 20 TPS
 Logger::info("Entering main server loop...");
 
 while (!$shutdown) {
-<<<<<<< HEAD
     // Handle signals (if available)
-=======
->>>>>>> 866a1c0 (...)
     if (\function_exists('pcntl_signal_dispatch')) {
         pcntl_signal_dispatch();
     }
diff --git a/src/Server.php b/src/Server.php
index 0655582..c5fe790 100644
--- a/src/Server.php
+++ b/src/Server.php
@@ -22,6 +22,7 @@ declare (strict_types=1);
 
 namespace watermossmc;
 
+use watermossmc\block\BlockRuntimeData;
 use watermossmc\command\CommandMap;
 use watermossmc\event\Event;
 use watermossmc\event\EventDispatcher;
@@ -69,6 +70,7 @@ final class Server
     public function boot(): void
     {
         block\BlockInitializer::init();
+		BlockRuntimeData::init(__DIR__ . "/../resources/canonical_block_states.nbt");
         item\ItemInitializer::init();
         foreach (\watermossmc\command\CommandRegistry::getCommands() as $commandClass) {
             /** @var \watermossmc\command\Command $command */
diff --git a/src/binary/McpeBinary.php b/src/binary/McpeBinary.php
index 5cc01ba..941415c 100644
--- a/src/binary/McpeBinary.php
+++ b/src/binary/McpeBinary.php
@@ -26,200 +26,476 @@ use RuntimeException;
 
 final class McpeBinary
 {
-    public static function writeByte(int $v): string
+
+    public static function writeByte(int $value): string
+    {
+        return chr($value & 0xFF);
+    }
+
+    public static function writeBool(bool $value): string
     {
-        return \chr($v & 0xFF);
+        return self::writeByte($value ? 1 : 0);
     }
 
-    public static function writeBool(bool $v): string
+    public static function writeShort(int $value): string
     {
-        return \chr($v ? 1 : 0);
+        return pack('n', $value & 0xFFFF);
     }
 
-    /** MCPE ushort = LITTLE endian */
-    public static function writeLShort(int $v): string
+    public static function writeLShort(int $value): string
     {
-        return pack('v', $v & 0xFFFF);
+        return pack('v', $value & 0xFFFF);
     }
 
-    /** MCPE int32 = BIG endian (YES, THIS IS CORRECT) */
-    public static function writeInt(int $v): string
+    public static function writeInt(int $value): string
     {
-        return pack('N', $v);
+        return pack('N', $value & 0xFFFFFFFF);
     }
 
-    public static function writeLInt(int $v): string
+    public static function writeLInt(int $value): string
     {
-        return pack('V', $v);
+        return pack('V', $value & 0xFFFFFFFF);
     }
 
-    /** MCPE float = LITTLE endian */
-    public static function writeFloat(float $v): string
+    public static function writeLong(int $value): string
     {
-        return pack('g', $v);
+        return pack('J', $value);
     }
 
-    /** MCPE long = LITTLE endian */
-    public static function writeLLong(int $v): string
+    public static function writeLLong(int $value): string
     {
-        return pack('P', $v);
+        return pack('q', $value);
     }
 
-    public static function writeString(string $v): string
+    public static function writeFloat(float $value): string
     {
-        return self::writeVarInt(\strlen($v)) . $v;
+        return pack('g', $value);
     }
 
-    public static function writeStringInt(string $v): string
+    public static function writeBFloat(float $value): string
     {
-        return self::writeInt(\strlen($v)) . $v;
+        return pack('G', $value);
+    }
+
+    public static function writeDouble(float $value): string
+    {
+        return pack('e', $value);
+    }
+
+    public static function writeBDouble(float $value): string
+    {
+        return pack('E', $value);
+    }
+
+    public static function writeString(string $value): string
+    {
+        return self::writeVarInt(strlen($value)) . $value;
+    }
+
+    public static function writeStringInt(string $value): string
+    {
+        return self::writeLInt(strlen($value)) . $value;
     }
 
     public static function writeVarInt(int $value): string
     {
-        $buf = '';
-        $v = $value & 0xFFFFFFFF;
+        $value &= 0xFFFFFFFF;
 
-        while (($v & ~0x7F) !== 0) {
-            $buf .= \chr(($v & 0x7F) | 0x80);
-            $v >>= 7;
-        }
+        $buffer = '';
+
+        do {
+            $byte = $value & 0x7F;
+            $value >>= 7;
+
+            if ($value !== 0) {
+                $byte |= 0x80;
+            }
 
-        return $buf . \chr($v);
+            $buffer .= chr($byte);
+        } while ($value !== 0);
+
+        return $buffer;
+    }
+
+    public static function writeUnsignedVarInt(int $value): string
+    {
+
+        return self::writeVarInt($value);
+    }
+
+    public static function writeSignedVarInt(int $value): string
+    {
+        $encoded = ($value << 1) ^ ($value >> 31);
+
+        return self::writeVarInt($encoded);
     }
 
-    public static function writeUnsignedVarLong(int $v): string
+    public static function writeUnsignedVarLong(int $value): string
     {
-        $buf = '';
+        $buffer = '';
+
         for ($i = 0; $i < 10; ++$i) {
-            $byte = $v & 0x7F;
-            $v >>= 7;
-            if ($v !== 0) {
+            $byte = $value & 0x7F;
+            $value >>= 7;
+
+            if ($value !== 0) {
                 $byte |= 0x80;
             }
-            $buf .= \chr($byte);
-            if ($v === 0) {
-                break;
+
+            $buffer .= chr($byte);
+
+            if ($value === 0) {
+                return $buffer;
             }
         }
-        return $buf;
+
+        throw new RuntimeException('Unsigned VarLong overflow');
     }
 
     public static function writeSignedVarLong(int $value): string
     {
-        $buf = '';
-        $v = $value;
+        $encoded = ($value << 1) ^ ($value >> 63);
 
-        while (($v & ~0x7F) !== 0) {
-            $buf .= \chr(($v & 0x7F) | 0x80);
-            $v >>= 7;
+        return self::writeUnsignedVarLong($encoded);
+    }
+
+    public static function writeUUID(string $uuid): string
+    {
+        $bytes = \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes();
+
+        return strrev(substr($bytes, 0, 8))
+            . strrev(substr($bytes, 8, 8));
+    }
+
+    private static function requireBytes(
+        string $buffer,
+        int $offset,
+        int $length
+    ): void {
+        if ($length < 0 || $offset < 0 || $offset + $length > strlen($buffer)) {
+            throw new RuntimeException(
+                "Unexpected end of buffer: need {$length} byte(s)"
+            );
         }
+    }
+
+    public static function readByte(string $buffer, int &$offset): int
+    {
+        self::requireBytes($buffer, $offset, 1);
 
-        return $buf . \chr($v);
+        return ord($buffer[$offset++]);
     }
 
-    public static function writeSignedVarInt(int $value): string
+    public static function readBool(string $buffer, int &$offset): bool
     {
-        $v = ($value << 1) ^ ($value >> 31);
-        return self::writeVarInt($v);
+        return self::readByte($buffer, $offset) !== 0;
     }
 
-    public static function writeUUID(string $uuid): string
+    public static function readShort(string $buffer, int &$offset): int
     {
-        $bytes = \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes();
-        return strrev(substr($bytes, 0, 8)) . strrev(substr($bytes, 8, 8));
+        self::requireBytes($buffer, $offset, 2);
+
+        $result = unpack('n', substr($buffer, $offset, 2));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack short');
+        }
+
+        $offset += 2;
+
+        return $result[1];
     }
 
-    public static function readByte(string $buf, int &$o): int
+    public static function readLShort(string $buffer, int &$offset): int
     {
-        return \ord($buf[$o++]);
+        self::requireBytes($buffer, $offset, 2);
+
+        $result = unpack('v', substr($buffer, $offset, 2));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack little-endian short');
+        }
+
+        $offset += 2;
+
+        return $result[1];
     }
 
-    public static function readBool(string $buf, int &$o): bool
+    public static function readInt(string $buffer, int &$offset): int
     {
-        return self::readByte($buf, $o) !== 0;
+        self::requireBytes($buffer, $offset, 4);
+
+        $result = unpack('N', substr($buffer, $offset, 4));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack int');
+        }
+
+        $offset += 4;
+
+        return $result[1];
     }
 
-    public static function readLShort(string $buf, int &$o): int
+    public static function readLInt(string $buffer, int &$offset): int
     {
-        $r = unpack('v', substr($buf, $o, 2));
-        if ($r === false || !isset($r[1])) {
-            throw new RuntimeException('unpack failed');
+        self::requireBytes($buffer, $offset, 4);
+
+        $result = unpack('V', substr($buffer, $offset, 4));
+
+        if ($result === false) {
+            throw new RuntimeException(
+                'Failed to unpack little-endian int'
+            );
         }
-        $o += 2;
-        /** @var int $value */
-        $value = $r[1];
-        return $value;
+
+        $offset += 4;
+
+        return $result[1];
     }
 
-    public static function readInt(string $buf, int &$o): int
+    public static function readLong(string $buffer, int &$offset): int
     {
-        $r = unpack('N', substr($buf, $o, 4));
-        if ($r === false || !isset($r[1])) {
-            throw new RuntimeException('unpack int failed');
+        self::requireBytes($buffer, $offset, 8);
+
+        $result = unpack('J', substr($buffer, $offset, 8));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack long');
         }
-        $o += 4;
-        /** @var int $value */
-        $value = $r[1];
-        return $value;
+
+        $offset += 8;
+
+        return $result[1];
     }
 
-    public static function readFloat(string $buf, int &$o): float
+    public static function readLLong(string $buffer, int &$offset): int
     {
-        $r = unpack('g', substr($buf, $o, 4));
-        if ($r === false || !isset($r[1])) {
-            throw new RuntimeException('unpack float failed');
+        self::requireBytes($buffer, $offset, 8);
+
+        $result = unpack('q', substr($buffer, $offset, 8));
+
+        if ($result === false) {
+            throw new RuntimeException(
+                'Failed to unpack little-endian long'
+            );
         }
-        $o += 4;
-        /** @var float $value */
-        $value = $r[1];
-        return $value;
+
+        $offset += 8;
+
+        return $result[1];
     }
 
-    public static function readString(string $buf, int &$o): string
+    public static function readFloat(string $buffer, int &$offset): float
     {
-        $len = self::readVarInt($buf, $o);
-        $v = substr($buf, $o, $len);
-        $o += $len;
-        return $v;
+        self::requireBytes($buffer, $offset, 4);
+
+        $result = unpack('g', substr($buffer, $offset, 4));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack float');
+        }
+
+        $offset += 4;
+
+        return $result[1];
+    }
+
+    public static function readBFloat(string $buffer, int &$offset): float
+    {
+        self::requireBytes($buffer, $offset, 4);
+
+        $result = unpack('G', substr($buffer, $offset, 4));
+
+        if ($result === false) {
+            throw new RuntimeException(
+                'Failed to unpack big-endian float'
+            );
+        }
+
+        $offset += 4;
+
+        return $result[1];
+    }
+
+    public static function readDouble(string $buffer, int &$offset): float
+    {
+        self::requireBytes($buffer, $offset, 8);
+
+        $result = unpack('e', substr($buffer, $offset, 8));
+
+        if ($result === false) {
+            throw new RuntimeException('Failed to unpack double');
+        }
+
+        $offset += 8;
+
+        return $result[1];
+    }
+
+    public static function readBDouble(string $buffer, int &$offset): float
+    {
+        self::requireBytes($buffer, $offset, 8);
+
+        $result = unpack('E', substr($buffer, $offset, 8));
+
+        if ($result === false) {
+            throw new RuntimeException(
+                'Failed to unpack big-endian double'
+            );
+        }
+
+        $offset += 8;
+
+        return $result[1];
+    }
+
+    public static function readString(string $buffer, int &$offset): string
+    {
+        $length = self::readVarInt($buffer, $offset);
+
+        if ($length < 0) {
+            throw new RuntimeException('Negative string length');
+        }
+
+        self::requireBytes($buffer, $offset, $length);
+
+        $value = substr($buffer, $offset, $length);
+        $offset += $length;
+
+        return $value;
+    }
+
+    public static function readStringInt(
+        string $buffer,
+        int &$offset
+    ): string {
+        $length = self::readLInt($buffer, $offset);
+
+        if ($length < 0) {
+            throw new RuntimeException('Negative string length');
+        }
+
+        self::requireBytes($buffer, $offset, $length);
+
+        $value = substr($buffer, $offset, $length);
+        $offset += $length;
+
+        return $value;
     }
 
-    public static function readVarInt(string $buf, int &$o): int
+    public static function readVarInt(string $buffer, int &$offset): int
     {
         $value = 0;
-        $shift = 0;
-        $len = \strlen($buf);
 
-        while (true) {
-            if ($o >= $len) {
-                throw new RuntimeException("VarInt overflow");
+        for ($i = 0; $i < 5; ++$i) {
+            $byte = self::readByte($buffer, $offset);
+
+            $value |= ($byte & 0x7F) << ($i * 7);
+
+            if (($byte & 0x80) === 0) {
+                return $value;
             }
+        }
+
+        throw new RuntimeException('VarInt32 overflow');
+    }
 
-            $b = \ord($buf[$o++]);
-            $value |= ($b & 0x7F) << $shift;
+    public static function readUnsignedVarInt(
+        string $buffer,
+        int &$offset
+    ): int {
+        return self::readVarInt($buffer, $offset);
+    }
+
+    public static function readSignedVarInt(
+        string $buffer,
+        int &$offset
+    ): int {
+        $value = self::readVarInt($buffer, $offset);
+
+        return ($value >> 1) ^ -($value & 1);
+    }
+
+    public static function readUnsignedVarLong(
+        string $buffer,
+        int &$offset
+    ): int {
+        $value = 0;
+
+        for ($i = 0; $i < 10; ++$i) {
+            $byte = self::readByte($buffer, $offset);
 
-            if (($b & 0x80) === 0) {
-                break;
+            if ($i === 9 && ($byte & 0x7E) !== 0) {
+                throw new RuntimeException('VarLong64 overflow');
             }
 
-            $shift += 7;
-            if ($shift > 35) {
-                throw new RuntimeException("VarInt too big");
+            $value |= ($byte & 0x7F) << ($i * 7);
+
+            if (($byte & 0x80) === 0) {
+                return $value;
             }
         }
 
-        return $value;
+        throw new RuntimeException('VarLong64 overflow');
     }
 
-    public static function readLInt(string $buf, int &$o): int
+    public static function readSignedVarLong(
+        string $buffer,
+        int &$offset
+    ): int {
+        $value = self::readUnsignedVarLong($buffer, $offset);
+
+        return ($value >> 1) ^ -($value & 1);
+    }
+
+    public static function readUUID(
+        string $buffer,
+        int &$offset
+    ): string {
+        self::requireBytes($buffer, $offset, 16);
+
+        $first = strrev(substr($buffer, $offset, 8));
+        $second = strrev(substr($buffer, $offset + 8, 8));
+
+        $offset += 16;
+
+        return \Ramsey\Uuid\Uuid::fromBytes($first . $second)
+            ->toString();
+    }
+
+    public static function writeBytes(string $value): string
     {
-        $r = unpack('V', substr($buf, $o, 4));
-        if ($r === false || !isset($r[1])) {
-            throw new RuntimeException('unpack lint failed');
-        }
-        $o += 4;
-        /** @var int $value */
-        $value = $r[1];
         return $value;
     }
-}
+
+    public static function readBytes(
+        string $buffer,
+        int &$offset,
+        int $length
+    ): string {
+        self::requireBytes($buffer, $offset, $length);
+
+        $value = substr($buffer, $offset, $length);
+        $offset += $length;
+
+        return $value;
+    }
+
+    public static function remaining(
+        string $buffer,
+        int $offset
+    ): int {
+        if ($offset < 0 || $offset > strlen($buffer)) {
+            throw new RuntimeException('Invalid buffer offset');
+        }
+
+        return strlen($buffer) - $offset;
+    }
+
+    public static function hasRemaining(
+        string $buffer,
+        int $offset
+    ): bool {
+        return $offset < strlen($buffer);
+    }
+}
\ No newline at end of file
diff --git a/src/block/Block.php b/src/block/Block.php
index b39e980..fe29737 100644
--- a/src/block/Block.php
+++ b/src/block/Block.php
@@ -30,10 +30,15 @@ abstract class Block
     public const STONE = 1;
     public const GRASS = 2;
     public const DIRT = 3;
-    public const BEDROCK = 7;
+    public const BEDROCK = 4;
 
     public function __construct(public readonly int $id, public readonly string $name) {}
 
+	public function getState(): BlockState
+	{
+		return new BlockState($this->name);
+	}
+
     public function onPlace(int $x, int $y, int $z): void
     {
         // Default: do nothing
diff --git a/src/block/types/AirBlock.php b/src/block/types/AirBlock.php
index 31115bc..65c3567 100644
--- a/src/block/types/AirBlock.php
+++ b/src/block/types/AirBlock.php
@@ -30,7 +30,7 @@ final class AirBlock extends Block
 {
     public function __construct()
     {
-        parent::__construct(0, 'Air');
+        parent::__construct(Block::AIR, 'minecraft:air');
     }
 
     public function onInteract(int $x, int $y, int $z, Player $player): void
diff --git a/src/block/types/BedrockBlock.php b/src/block/types/BedrockBlock.php
index 1822ddf..1d762e5 100644
--- a/src/block/types/BedrockBlock.php
+++ b/src/block/types/BedrockBlock.php
@@ -28,7 +28,7 @@ final class BedrockBlock extends Block
 {
     public function __construct()
     {
-        parent::__construct(7, 'Bedrock');
+        parent::__construct(Block::BEDROCK, 'minecraft:bedrock');
     }
 
     public function onBreak(int $x, int $y, int $z): void
diff --git a/src/block/types/DirtBlock.php b/src/block/types/DirtBlock.php
index 2bf97bd..669769f 100644
--- a/src/block/types/DirtBlock.php
+++ b/src/block/types/DirtBlock.php
@@ -28,6 +28,6 @@ final class DirtBlock extends Block
 {
     public function __construct()
     {
-        parent::__construct(3, 'Dirt');
+        parent::__construct(Block::DIRT, 'Dirt');
     }
 }
diff --git a/src/block/types/GrassBlock.php b/src/block/types/GrassBlock.php
index 89b1fe0..2f2590d 100644
--- a/src/block/types/GrassBlock.php
+++ b/src/block/types/GrassBlock.php
@@ -28,6 +28,6 @@ final class GrassBlock extends Block
 {
     public function __construct()
     {
-        parent::__construct(2, 'Grass');
+        parent::__construct(Block::GRASS, 'Grass');
     }
 }
diff --git a/src/block/types/StoneBlock.php b/src/block/types/StoneBlock.php
index b75b9c4..069caf7 100644
--- a/src/block/types/StoneBlock.php
+++ b/src/block/types/StoneBlock.php
@@ -28,6 +28,6 @@ final class StoneBlock extends Block
 {
     public function __construct()
     {
-        parent::__construct(1, 'Stone');
+        parent::__construct(Block::STONE, 'minecraft:stone');
     }
 }
diff --git a/src/crypto/EncryptionContext.php b/src/crypto/EncryptionContext.php
index f8ed4a6..34d5a14 100644
--- a/src/crypto/EncryptionContext.php
+++ b/src/crypto/EncryptionContext.php
@@ -65,17 +65,17 @@ final class EncryptionContext
 
         $payload = substr($decrypted, 0, -8);
         $clientChecksum = substr($decrypted, -8);
-        $expected = $this->computeChecksum($payload, $this->decryptionCounter);
+		$counter = $this->decryptionCounter++;
+        $expected = $this->computeChecksum($payload, $counter);
 
         if (!hash_equals($expected, $clientChecksum)) {
             throw new RuntimeException(
-                "Checksum mismatch! counter=" . $this->decryptionCounter .
+                "Checksum mismatch! counter=" . $counter .
                 " expected=" . bin2hex($expected) .
                 " actual=" . bin2hex($clientChecksum)
             );
         }
 
-        $this->decryptionCounter++;
         return $payload;
     }
 
diff --git a/src/mcpe/PacketHandler.php b/src/mcpe/PacketHandler.php
index b5c1dc3..7273e8f 100644
--- a/src/mcpe/PacketHandler.php
+++ b/src/mcpe/PacketHandler.php
@@ -26,6 +26,7 @@ use RuntimeException;
 use Socket;
 use Throwable;
 use watermossmc\binary\Binary;
+use watermossmc\block\BlockRuntimeData;
 use watermossmc\crypto\Crypto;
 use watermossmc\event\PlayerJoinEvent;
 use watermossmc\event\PlayerMoveEvent;
@@ -51,6 +52,7 @@ use watermossmc\mcpe\protocol\PlayerHotbar;
 use watermossmc\mcpe\protocol\PlayerList;
 use watermossmc\mcpe\protocol\PlayStatus;
 use watermossmc\mcpe\protocol\ProtocolInfo;
+use watermossmc\mcpe\protocol\RequestChunkRadius;
 use watermossmc\mcpe\protocol\RequestNetworkSettings;
 use watermossmc\mcpe\protocol\ResourcePackClientResponse;
 use watermossmc\mcpe\protocol\ResourcePacksInfo;
@@ -334,9 +336,10 @@ final class PacketHandler
                 case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET:
                     // RequestChunkRadius
                     Logger::debug("[0x45] RequestChunkRadius received");
-                    $world = self::getWorld();
-                    $spawn = $world->getSpawnPosition();
-                    SetSpawnPosition::send($session, $socket, $spawn['x'], $spawn['y'], $spawn['z']);
+                    $data = RequestChunkRadius::read($packet, $o);
+                    $session->setChunkRadius($data['radius']);
+                    $session->setMaxChunkRadius($data['maxRadius']);
+                    Logger::debug("[0x45] Requested radius={$data['radius']}, maxRadius={$data['maxRadius']}");
                     self::sendSpawnChunks($session, $socket);
                     PlayStatus::sendPlayerSpawn($session, $socket);
                     $session->enterPlay();
@@ -430,9 +433,6 @@ final class PacketHandler
                 Logger::debug("[Sequence] Sending BiomeDefinitionList...");
                 BiomeDefinitionList::send($s, $sock);
 
-                Logger::debug("[Sequence] Sending SetTime...");
-                SetTime::send($s, $sock, $world->getDayTime());
-
                 Logger::debug("[Sequence] Sending UpdateAttributes...");
                 UpdateAttributes::send($player, $sock);
 
@@ -556,16 +556,15 @@ final class PacketHandler
             return;
         }
         $spawn = $world->getSpawnPosition();
+        $radius = $s->getChunkRadius();
         $centerChunkX = self::blockToChunk($spawn['x']);
         $centerChunkZ = self::blockToChunk($spawn['z']);
-        // The Bedrock protocol often requires sending a "cache enabled" boolean
-        // and a list of blob hashes before the chunks themselves.
-        // However, LevelChunk::send in this codebase handles the individual chunk.
-        // We need to ensure the chunk encoding itself respects the cache setting.
-        for ($x = $centerChunkX - 2; $x <= $centerChunkX + 2; $x++) {
-            for ($z = $centerChunkZ - 2; $z <= $centerChunkZ + 2; $z++) {
+		$converter = BlockRuntimeData::getConverter();
+
+        for ($x = $centerChunkX - $radius; $x <= $centerChunkX + $radius; $x++) {
+            for ($z = $centerChunkZ - $radius; $z <= $centerChunkZ + $radius; $z++) {
                 $chunk = $world->getChunk($x, $z);
-                LevelChunk::send($s, $sock, $x, $z, $chunk->encode(), $chunk->getSubChunkCount());
+                LevelChunk::send($s, $sock, $x, $z, $chunk->encode($converter), $chunk->getSubChunkCount());
             }
         }
     }
@@ -596,4 +595,3 @@ final class PacketHandler
         }
     }
 }
-use watermossmc\mcpe\protocol\{AvailableActorIdentifiers, AvailableCommands, BiomeDefinitionList, ClientToServerHandshake, CraftingData, CreativeContent, Disconnect, InventoryContent, ItemRegistry, LevelChunk, Login, NetworkSettings, PlayStatus, PlayerHotbar, PlayerList, ProtocolInfo, RequestNetworkSettings, ResourcePackClientResponse, ResourcePackStack, ResourcePacksInfo, ServerToClientHandshake, SetActorData, StartGame, UpdateAbilities, UpdateAdventureSettings, UpdateAttributes};
diff --git a/src/mcpe/network/Session.php b/src/mcpe/network/Session.php
index f6fc4ac..1987865 100644
--- a/src/mcpe/network/Session.php
+++ b/src/mcpe/network/Session.php
@@ -145,6 +145,10 @@ final class Session
 
     private bool $cacheEnabled = false;
 
+    private int $chunkRadius = 0;
+
+    private int $maxChunkRadius = 0;
+
     public function __construct(string $addr, int $port)
     {
         $this->address = $addr;
@@ -606,4 +610,24 @@ final class Session
     {
         $this->cacheEnabled = $v;
     }
+
+    public function setChunkRadius(int $radius): void
+    {
+        $this->chunkRadius = $radius;
+    }
+
+    public function getChunkRadius(): int
+    {
+        return $this->chunkRadius;
+    }
+
+    public function setMaxChunkRadius(int $maxRadius): void
+    {
+        $this->maxChunkRadius = $maxRadius;
+    }
+
+    public function getMaxChunkRadius(): int
+    {
+        return $this->maxChunkRadius;
+    }
 }
diff --git a/src/mcpe/protocol/CraftingData.php b/src/mcpe/protocol/CraftingData.php
index 23b6b89..22d4937 100644
--- a/src/mcpe/protocol/CraftingData.php
+++ b/src/mcpe/protocol/CraftingData.php
@@ -32,6 +32,13 @@ final class CraftingData extends Packet
     {
         $payload = '';
         $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
+        $payload .= Binary::writeVarInt(0);
         // recipesWithTypeIds count
         $payload .= Binary::writeVarInt(0);
         // potionTypeRecipes count
diff --git a/src/mcpe/protocol/LevelChunk.php b/src/mcpe/protocol/LevelChunk.php
index 2b95379..daf833e 100644
--- a/src/mcpe/protocol/LevelChunk.php
+++ b/src/mcpe/protocol/LevelChunk.php
@@ -18,7 +18,7 @@
  * @link https://github.com/watermossmc/WatermossMC
  */
 
-declare (strict_types=1);
+declare(strict_types=1);
 
 namespace watermossmc\mcpe\protocol;
 
@@ -29,32 +29,54 @@ use watermossmc\mcpe\network\Session;
 
 final class LevelChunk extends Packet
 {
-    public static function send(Session $s, Socket $sock, int $chunkX, int $chunkZ, string $chunkData, int $subChunkCount): void
-    {
-        $p = Binary::writeVarInt($chunkX);
-        $p .= Binary::writeVarInt($chunkZ);
-        $p .= Binary::writeVarInt(0);
-        // dimension ID
-        $p .= Binary::writeVarInt($subChunkCount);
-        $p .= Binary::writeBool($s->isCacheEnabled());
-        // cache enabled
-        $p .= McpeBinary::writeString($chunkData);
-        $p .= McpeBinary::writeString("");
-        self::sendBatch(ProtocolInfo::LEVEL_CHUNK_PACKET, $p, $s, $sock);
-    }
+    private const MAX_BLOB_HASHES = 64;
+
+    public static function send(
+        Session $s,
+        Socket $sock,
+        int $chunkX,
+        int $chunkZ,
+        string $chunkData,
+        int $subChunkCount
+    ): void {
+        $p = McpeBinary::writeUnsignedVarInt($chunkX);
+        $p .= McpeBinary::writeUnsignedVarInt($chunkZ);
+
+        // Dimension ID
+        $p .= McpeBinary::writeSignedVarInt(0);
 
-    private static function writeBiomeData(): string
-    {
-        $out = '';
-        $palette = [1];
-        $bits = 1;
-        $out .= Binary::writeByte($bits);
-        $words = intdiv(256 * $bits + 31, 32);
-        $out .= str_repeat("\x00\x00\x00\x00", $words);
-        $out .= Binary::writeVarInt(\count($palette));
-        foreach ($palette as $biomeId) {
-            $out .= Binary::writeVarInt($biomeId);
+        // SubChunk count
+        $p .= McpeBinary::writeUnsignedVarInt($subChunkCount);
+
+        $clientRequestSubChunkLimit = null;
+
+        $p .= Binary::writeBool($clientRequestSubChunkLimit !== null);
+
+        if ($clientRequestSubChunkLimit !== null) {
+            $p .= McpeBinary::writeSignedVarInt($clientRequestSubChunkLimit);
         }
-        return $out;
+
+        // Cache enabled
+        $cacheEnabled = $s->isCacheEnabled();
+        $p .= Binary::writeBool($cacheEnabled);
+
+        // Used blob hashes
+        $usedBlobHashes = [];
+
+        $p .= McpeBinary::writeUnsignedVarInt(count($usedBlobHashes));
+
+        foreach ($usedBlobHashes as $hash) {
+            $p .= McpeBinary::writeUnsignedLong($hash);
+        }
+
+        // Extra payload
+        $p .= McpeBinary::writeString($chunkData);
+
+        self::sendBatch(
+            ProtocolInfo::LEVEL_CHUNK_PACKET,
+            $p,
+            $s,
+            $sock
+        );
     }
-}
+}
\ No newline at end of file
diff --git a/src/mcpe/protocol/ResourcePackClientResponse.php b/src/mcpe/protocol/ResourcePackClientResponse.php
index c0e2022..203a9ed 100644
--- a/src/mcpe/protocol/ResourcePackClientResponse.php
+++ b/src/mcpe/protocol/ResourcePackClientResponse.php
@@ -22,25 +22,29 @@ declare (strict_types=1);
 
 namespace watermossmc\mcpe\protocol;
 
-use watermossmc\binary\Binary;
+use watermossmc\binary\McpeBinary;
 
 final class ResourcePackClientResponse extends Packet
 {
-    public const STATUS_REFUSED = 1;
-    public const STATUS_SEND_PACKS = 2;
-    public const STATUS_HAVE_ALL_PACKS = 3;
-    public const STATUS_COMPLETED = 4;
+    public const STATUS_REFUSED = 0;
+    public const STATUS_SEND_PACKS = 1;
+    public const STATUS_HAVE_ALL_PACKS = 2;
+    public const STATUS_COMPLETED = 3;
 
     /**
      * @return array{status:int}
      */
     public static function read(string $p, int &$o): array
     {
-        $status = Binary::readByte($p, $o);
-        $count = Binary::readLShort($p, $o);
+        $status = McpeBinary::readVarInt($p, $o);
+        McpeBinary::readString($p, $o);
+
         $packs = [];
-        for ($i = 0; $i < $count; $i++) {
-            $packs[] = Binary::readString($p, $o);
+        if($status === self::STATUS_SEND_PACKS){
+          $count = McpeBinary::readVarInt($p, $o);
+          for ($i = 0; $i < $count; $i++) {
+            $packs[] = McpeBinary::readString($p, $o);
+          }
         }
         return ['status' => $status, 'packs' => $packs];
     }
diff --git a/src/mcpe/protocol/ResourcePacksInfo.php b/src/mcpe/protocol/ResourcePacksInfo.php
index 2d0a0b1..470a577 100644
--- a/src/mcpe/protocol/ResourcePacksInfo.php
+++ b/src/mcpe/protocol/ResourcePacksInfo.php
@@ -43,7 +43,7 @@ final class ResourcePacksInfo extends Packet
         $p .= Binary::writeUUID('00000000-0000-0000-0000-000000000000'); // worldTemplateId
         $p .= McpeBinary::writeString(""); // worldTemplateVersion
 
-        $p .= Binary::writeLShort(\count($packs));
+        $p .= McpeBinary::writeUnsignedVarInt(\count($packs));
 
         Logger::debug("[ResourcePacks] Raw payload hex: " . bin2hex($p));
 
diff --git a/src/mcpe/protocol/StartGame.php b/src/mcpe/protocol/StartGame.php
index 033b3de..2e5b757 100644
--- a/src/mcpe/protocol/StartGame.php
+++ b/src/mcpe/protocol/StartGame.php
@@ -22,14 +22,12 @@ declare (strict_types=1);
 
 namespace watermossmc\mcpe\protocol;
 
-use function count;
-
 use Socket;
 use watermossmc\binary\McpeBinary;
 use watermossmc\mcpe\network\Session;
-use watermossmc\mcpe\protocol\types\Experiments;
 use watermossmc\player\Player;
 use watermossmc\util\Config;
+use watermossmc\util\Logger;
 use watermossmc\world\World;
 
 final class StartGame extends Packet
@@ -41,189 +39,148 @@ final class StartGame extends Packet
         $spawn = $world->getSpawnPosition();
         $position = $player->getPosition();
         $rotation = $player->getRotation();
+        
         $payload = '';
-        // Actor IDs
-        // actorUniqueId must be the XUID (unique permanent ID), NOT the runtimeId
+        
+        // --- Actor IDs ---
         $xuid = $player->session->getXuid() ?? '0';
-        $payload .= McpeBinary::writeSignedVarLong((int) $xuid);
-        // actorRuntimeId is the session-specific ID
-        $payload .= McpeBinary::writeUnsignedVarLong($player->session->getRuntimeId());
-        // playerGamemode - Signed VarInt
-        $payload .= McpeBinary::writeSignedVarInt($player->getGameMode());
-        // playerGamemode
-        // Player Position & Rotation
+        $payload .= McpeBinary::writeSignedVarLong((int) $xuid); // actorUniqueId
+        $payload .= McpeBinary::writeUnsignedVarLong($player->session->getRuntimeId()); // actorRuntimeId
+        $payload .= McpeBinary::writeSignedVarInt($player->getGameMode()); // playerGamemode
+
+        // --- Player Position & Rotation ---
         $payload .= McpeBinary::writeFloat($position['x']);
         $payload .= McpeBinary::writeFloat($position['y']);
         $payload .= McpeBinary::writeFloat($position['z']);
         $payload .= McpeBinary::writeFloat($rotation['pitch']);
         $payload .= McpeBinary::writeFloat($rotation['yaw']);
-        // LevelSettings
+        
+        // ==========================================
+        // --- LevelSettings ---
+        // ==========================================
         $payload .= McpeBinary::writeLLong($seed);
-        // seed
+        
         // SpawnSettings (biomeType, biomeName, dimension)
         $payload .= McpeBinary::writeLShort(0); // biomeType = DEFAULT (0)
-        $payload .= McpeBinary::writeString(''); // biome name
+        $payload .= McpeBinary::writeString(''); // biomeName
         $payload .= McpeBinary::writeSignedVarInt(0); // dimension = overworld (0)
-        $payload .= McpeBinary::writeSignedVarInt(1); // generator = 1
+
+        $payload .= McpeBinary::writeSignedVarInt(1); // generator = 1 (Overworld)
         $payload .= McpeBinary::writeSignedVarInt($world->getGameType()); // worldGamemode
-        $payload .= McpeBinary::writeBool(false);
-        // hardcore
-        $payload .= McpeBinary::writeSignedVarInt(1);
-        // difficulty
+        $payload .= McpeBinary::writeBool(false); // hardcore
+        $payload .= McpeBinary::writeSignedVarInt(1); // difficulty (1 = Normal)
+        
+        // BlockPosition (Spawn)
         $payload .= McpeBinary::writeSignedVarInt($spawn['x']);
-        // spawnPosition x
         $payload .= McpeBinary::writeSignedVarInt($spawn['y']);
-        // spawnPosition y
         $payload .= McpeBinary::writeSignedVarInt($spawn['z']);
-        // spawnPosition z
-        $payload .= McpeBinary::writeBool(true);
-        // hasAchievementsDisabled
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // editorWorldType = NON_EDITOR
-        $payload .= McpeBinary::writeBool(false);
-        // createdInEditorMode
-        $payload .= McpeBinary::writeBool(false);
-        // exportedFromEditorMode
-        $payload .= McpeBinary::writeSignedVarInt(-1);
-        // time
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // eduEditionOffer
-        $payload .= McpeBinary::writeBool(false);
-        // hasEduFeaturesEnabled
-        $payload .= McpeBinary::writeString('');
-        // eduProductUUID
-        $payload .= McpeBinary::writeFloat(0.0);
-        // rainLevel
-        $payload .= McpeBinary::writeFloat(0.0);
-        // lightningLevel
-        $payload .= McpeBinary::writeBool(false);
-        // hasConfirmedPlatformLockedContent
-        $payload .= McpeBinary::writeBool(true);
-        // isMultiplayerGame
-        $payload .= McpeBinary::writeBool(true);
-        // hasLANBroadcast
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // xboxLiveBroadcastMode
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // platformBroadcastMode
-        $payload .= McpeBinary::writeBool(true);
-        // commandsEnabled
-        $payload .= McpeBinary::writeBool(true);
-        // texturePacksRequired
-        // GameRules
-        $payload .= McpeBinary::writeVarInt(0);
-        // count
+        
+        $payload .= McpeBinary::writeBool(true); // hasAchievementsDisabled
+        $payload .= McpeBinary::writeSignedVarInt(0); // editorWorldType = NON_EDITOR
+        $payload .= McpeBinary::writeBool(false); // createdInEditorMode
+        $payload .= McpeBinary::writeBool(false); // exportedFromEditorMode
+        $payload .= McpeBinary::writeSignedVarInt(-1); // time
+        $payload .= McpeBinary::writeUnsignedVarInt(0); // eduEditionOffer
+        $payload .= McpeBinary::writeBool(false); // hasEduFeaturesEnabled
+        $payload .= McpeBinary::writeString(''); // eduProductUUID
+        $payload .= McpeBinary::writeFloat(0.0); // rainLevel
+        $payload .= McpeBinary::writeFloat(0.0); // lightningLevel
+        $payload .= McpeBinary::writeBool(false); // hasConfirmedPlatformLockedContent
+        $payload .= McpeBinary::writeBool(true); // isMultiplayerGame
+        $payload .= McpeBinary::writeBool(true); // hasLANBroadcast
+        $payload .= McpeBinary::writeSignedVarInt(0); // xboxLiveBroadcastMode (0 = Public)
+        $payload .= McpeBinary::writeSignedVarInt(0); // platformBroadcastMode (0 = Public)
+        $payload .= McpeBinary::writeBool(true); // commandsEnabled
+        $payload .= McpeBinary::writeBool(false); // isTexturePacksRequired
+        
+        // GameRules (Count = 0)
+        $payload .= McpeBinary::writeUnsignedVarInt(0); 
+        
         // Experiments
-        $payload .= McpeBinary::writeLInt(0);
-        // experiments count (LE unsigned int)
-        $payload .= McpeBinary::writeBool(false);
-        // hasPreviouslyUsedExperiments
-        $payload .= McpeBinary::writeBool(false);
-        // hasBonusChestEnabled
-        $payload .= McpeBinary::writeBool(false);
-        // hasStartWithMapEnabled
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // defaultPlayerPermission
-        $payload .= McpeBinary::writeByte(1);
-        // serverChunkTickRadius
-        $payload .= McpeBinary::writeLInt(4);
-        // hasLockedBehaviorPack
-        $payload .= McpeBinary::writeBool(false);
-        // hasLockedResourcePack
-        $payload .= McpeBinary::writeBool(false);
-        // isFromLockedWorldTemplate
-        $payload .= McpeBinary::writeBool(false);
-        // useMsaGamertagsOnly
-        $payload .= McpeBinary::writeBool(false);
-        // isFromWorldTemplate
-        $payload .= McpeBinary::writeBool(false);
-        // isWorldTemplateOptionLocked
-        $payload .= McpeBinary::writeBool(false);
-        // onlySpawnV1Villagers
-        $payload .= McpeBinary::writeBool(false);
-        // disablePersona
-        $payload .= McpeBinary::writeBool(false);
-        // disableCustomSkins
-        $payload .= McpeBinary::writeBool(false);
-        // muteEmoteAnnouncements
-        $payload .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK);
-        // vanillaVersion
-        $payload .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK);
-        // limitedWorldWidth
-        $payload .= McpeBinary::writeLInt(0);
-        // limitedWorldLength
-        $payload .= McpeBinary::writeLInt(0);
-        // isNewNether
-        $payload .= McpeBinary::writeBool(true);
-        // Optional EduSharedUriResource
-        $payload .= McpeBinary::writeBool(false);
-        // Optional experimentalGameplayOverride
-        $payload .= McpeBinary::writeBool(false);
-        // chatRestrictionLevel
-        $payload .= McpeBinary::writeByte(0);
-        $payload .= McpeBinary::writeBool(false);
-        // disablePlayerInteractions
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // serverEditorConnectionPolicy
-        $payload .= McpeBinary::writeBool(false);
-        // allowAnonymousBlockDropsInEditorWorlds
+        $payload .= McpeBinary::writeLInt(0); // experiments count
+        $payload .= McpeBinary::writeBool(false); // hasPreviouslyUsedExperiments
+        
+        $payload .= McpeBinary::writeBool(false); // hasBonusChestEnabled
+        $payload .= McpeBinary::writeBool(false); // hasStartWithMapEnabled
+
+        $payload .= McpeBinary::writeByte(1); // defaultPlayerPermission (1 = Member)
+        $payload .= McpeBinary::writeLInt(4); // serverChunkTickRadius
+        
+        $payload .= McpeBinary::writeBool(false); // hasLockedBehaviorPack
+        $payload .= McpeBinary::writeBool(false); // hasLockedResourcePack
+        $payload .= McpeBinary::writeBool(false); // isFromLockedWorldTemplate
+        $payload .= McpeBinary::writeBool(false); // useMsaGamertagsOnly
+        $payload .= McpeBinary::writeBool(false); // isFromWorldTemplate
+        $payload .= McpeBinary::writeBool(false); // isWorldTemplateOptionLocked
+        $payload .= McpeBinary::writeBool(false); // onlySpawnV1Villagers
+        $payload .= McpeBinary::writeBool(false); // disablePersona
+        $payload .= McpeBinary::writeBool(false); // disableCustomSkins
+        $payload .= McpeBinary::writeBool(false); // muteEmoteAnnouncements
+        $payload .= McpeBinary::writeString(ProtocolInfo::MINECRAFT_VERSION_NETWORK); // vanillaVersion
+        $payload .= McpeBinary::writeLInt(0); // limitedWorldWidth
+        $payload .= McpeBinary::writeLInt(0); // limitedWorldLength
+        $payload .= McpeBinary::writeBool(true); // isNewNether
+        
+        // EduSharedUriResource
+        $payload .= McpeBinary::writeString(""); // buttonName
+        $payload .= McpeBinary::writeString(""); // linkUri
+        
+        // experimentalGameplayOverride
+        $payload .= McpeBinary::writeBool(false); // hasValue = false
+        
+        $payload .= McpeBinary::writeByte(0); // chatRestrictionLevel
+        $payload .= McpeBinary::writeBool(false); // disablePlayerInteractions
+        $payload .= McpeBinary::writeSignedVarInt(0); // serverEditorConnectionPolicy
+        $payload .= McpeBinary::writeBool(false); // allowAnonymousBlockDropsInEditorWorlds
+        // ==========================================
+        // --- End of LevelSettings ---
+        // ==========================================
+
         // World Identification
-        $payload .= McpeBinary::writeString("");
-        // levelId
-        $payload .= McpeBinary::writeString($worldName);
-        // worldName
-        $payload .= McpeBinary::writeString("");
-        // premiumWorldTemplateId
-        $payload .= McpeBinary::writeBool(false);
-        // isTrial
+        $payload .= McpeBinary::writeString(""); // levelId
+        $payload .= McpeBinary::writeString($worldName); // worldName
+        $payload .= McpeBinary::writeString(""); // premiumWorldTemplateId
+        $payload .= McpeBinary::writeBool(false); // isTrial
+        
         // PlayerMovementSettings
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // rewindHistorySize
-        $payload .= McpeBinary::writeBool(false);
-        // serverAuthoritativeBlockBreaking
+        $payload .= McpeBinary::writeSignedVarInt(0); // rewindHistorySize
+        $payload .= McpeBinary::writeBool(true); // serverAuthoritativeBlockBreaking
+        
         // Tick & Seed
-        $payload .= McpeBinary::writeLLong(0);
-        // currentTick
-        $payload .= McpeBinary::writeSignedVarInt(0);
-        // enchantmentSeed
+        $payload .= McpeBinary::writeLLong(0); // currentTick
+        $payload .= McpeBinary::writeSignedVarInt(0); // enchantmentSeed
+        
         // Block Palette
-        $payload .= McpeBinary::writeVarInt(0);
-        // count
+        $payload .= McpeBinary::writeUnsignedVarInt(0); // blockPalette Count
+        
         // Server Info
-        $payload .= McpeBinary::writeString("");
-        // multiplayerCorrelationId
-        $payload .= McpeBinary::writeBool(false);
-        // enableNewInventorySystem
-        $payload .= McpeBinary::writeString("WatermossMC");
-        // serverSoftwareVersion
-        // Player Actor Properties (NBT)
-        $payload .= McpeBinary::writeVarInt(0);
-        // length of NBT bytes
+        $payload .= McpeBinary::writeString(""); // multiplayerCorrelationId
+        $payload .= McpeBinary::writeBool(true); // enableNewInventorySystem
+        $payload .= McpeBinary::writeString("WatermossMC"); // serverSoftwareVersion
+        
+        // Player Actor Properties
+        $payload .= "\x0a\x00\x00";
+        
         // Checksum & Template
-        $payload .= McpeBinary::writeLLong(0);
-        // blockPaletteChecksum
-        $payload .= McpeBinary::writeUUID("00000000-0000-0000-0000-000000000000");
-        // worldTemplateId
-        $payload .= McpeBinary::writeBool(false);
-        // enableClientSideChunkGeneration
-        $payload .= McpeBinary::writeBool(false);
-        // blockNetworkIdsAreHashes
-        // Permissions & Logging
-        $payload .= McpeBinary::writeBool(false);
-        // networkPermissions (simplified bool)
-        $payload .= McpeBinary::writeBool(false);
-        // isLoggingChat
-        // ServerJoinInformation
-        $payload .= McpeBinary::writeBool(false);
+        $payload .= McpeBinary::writeLLong(0); // blockPaletteChecksum
+        $payload .= McpeBinary::writeUUID("00000000-0000-0000-0000-000000000000"); // worldTemplateId
+        $payload .= McpeBinary::writeBool(false); // enableClientSideChunkGeneration
+        $payload .= McpeBinary::writeBool(false); // blockNetworkIdsAreHashes
+        
+        // NetworkPermissions
+        $payload .= McpeBinary::writeBool(false); // serverAuthSounds
+        
+        // ServerJoinInformation (Optional, hasValue = false)
+        $payload .= McpeBinary::writeBool(false); 
+        
         // ServerTelemetryData
-        $payload .= McpeBinary::writeString("");
-        // serverId
-        $payload .= McpeBinary::writeString("");
-        // scenarioId
-        $payload .= McpeBinary::writeString("");
-        // worldId
-        $payload .= McpeBinary::writeString("");
-        // ownerId
+        $payload .= McpeBinary::writeString(""); // serverId
+        $payload .= McpeBinary::writeString(""); // scenarioId
+        $payload .= McpeBinary::writeString(""); // worldId
+        $payload .= McpeBinary::writeString(""); // ownerId
+
+      Logger::debug(bin2hex($payload));
+        
         self::sendBatch(ProtocolInfo::START_GAME_PACKET, $payload, $s, $sock);
     }
 }
diff --git a/src/nbt/NBT.php b/src/nbt/NBT.php
index e78d6a4..569309d 100644
--- a/src/nbt/NBT.php
+++ b/src/nbt/NBT.php
@@ -1,24 +1,6 @@
 <?php
 
-/*
- * __        __    _                                    __  __  ____
- * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
- *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
- *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
- *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
- *
- * WatermossMC
- *
- * This program is free software: you can redistribute it and/or modify
- * it under the terms of the GNU Lesser General Public License as published by
- * the Free Software Foundation, either version 3 of the License, or
- * (at your option) any later version.
- *
- * @author WatermossMC Team
- * @link https://github.com/watermossmc/WatermossMC
- */
-
-declare (strict_types=1);
+declare(strict_types=1);
 
 namespace watermossmc\nbt;
 
@@ -26,372 +8,483 @@ use RuntimeException;
 
 final class NBT
 {
-    public const TAG_END = 0;
-    public const TAG_BYTE = 1;
-    public const TAG_SHORT = 2;
-    public const TAG_INT = 3;
-    public const TAG_LONG = 4;
-    public const TAG_FLOAT = 5;
-    public const TAG_DOUBLE = 6;
-    public const TAG_BYTE_ARRAY = 7;
-    public const TAG_STRING = 8;
-    public const TAG_LIST = 9;
-    public const TAG_COMPOUND = 10;
-    public const TAG_INT_ARRAY = 11;
-    public const TAG_LONG_ARRAY = 12;
-
-    /**
-     * @param array<int|string, mixed> $data
-     */
-    public static function compound(array $data): string
-    {
-        $buf = \chr(self::TAG_COMPOUND);
-        $buf .= self::writeString('');
-        foreach ($data as $name => $value) {
-            $buf .= self::writeNamedTag((string) $name, $value);
-        }
-        return $buf . \chr(self::TAG_END);
-    }
-
-    /**
-     * @param string $data
-     * @return array<string, mixed>
-     */
-    public static function parse(string $data): array
-    {
-        $offset = 0;
-        [$tag, $name, $value] = self::readNamedTag($data, $offset);
-        if ($tag !== self::TAG_COMPOUND) {
-            throw new RuntimeException('NBT root tag must be a compound');
-        }
-        if (!\is_array($value)) {
-            throw new RuntimeException('NBT root value must be a compound');
-        }
-        return $value;
-    }
-
-    private static function writeNamedTag(string $name, mixed $value): string
-    {
-        [$tag, $payload] = self::detectTag($value);
-        return \chr($tag) . self::writeString($name) . $payload;
-    }
-
-    /**
-     * @param array<mixed> $value
-     * @return array{__nbt_type: int, __nbt_value: array<mixed>}
-     */
-    public static function tagCompound(array $value): array
-    {
-        return ['__nbt_type' => self::TAG_COMPOUND, '__nbt_value' => $value];
-    }
-
-    /**
-     * @param array<mixed> $value
-     * @return array{__nbt_type: int, __nbt_value: array<mixed>}
-     */
-    public static function tagList(array $value): array
-    {
-        return ['__nbt_type' => self::TAG_LIST, '__nbt_value' => $value];
-    }
-
-    /**
-     * @return array{0:int,1:string}
-     */
-    private static function detectTag(mixed $value): array
-    {
-        if (\is_array($value) && isset($value['__nbt_type'], $value['__nbt_value'])) {
-            return match ($value['__nbt_type']) {
-                self::TAG_COMPOUND => [self::TAG_COMPOUND, self::writeCompoundPayload($value['__nbt_value'])],
-                self::TAG_LIST => self::writeList($value['__nbt_value']),
-                default => throw new RuntimeException('Unsupported explicit NBT tag'),
-            };
-        }
-        if (\is_int($value)) {
-            if ($value < -2147483648 || $value > 2147483647) {
-                return [self::TAG_LONG, self::writeLong($value)];
-            }
-            return [self::TAG_INT, self::writeInt($value)];
-        }
-        if (\is_float($value)) {
-            return [self::TAG_FLOAT, self::writeFloat($value)];
-        }
-        if (\is_string($value)) {
-            return [self::TAG_STRING, self::writeString($value)];
-        }
-        if (\is_bool($value)) {
-            return [self::TAG_BYTE, \chr($value ? 1 : 0)];
-        }
-        if (\is_array($value)) {
-            if (self::isList($value)) {
-                return self::writeList($value);
-            }
-            return [self::TAG_COMPOUND, self::writeCompoundPayload($value)];
-        }
-        throw new RuntimeException('Unsupported NBT type');
-    }
-
-    /**
-     * @param array<mixed, mixed> $data
-     */
-    private static function writeCompoundPayload(array $data): string
-    {
-        $buf = '';
-        foreach ($data as $name => $value) {
-            $buf .= self::writeNamedTag((string) $name, $value);
-        }
-        return $buf . \chr(self::TAG_END);
-    }
-
-    /**
-     * @param array<mixed> $list
-     * @return array{0:int,1:string}
-     */
-    private static function writeList(array $list): array
-    {
-        if ($list === []) {
-            return [self::TAG_LIST, \chr(self::TAG_END) . pack('N', 0)];
-        }
-        [$childTag] = self::detectTag($list[0]);
-        $buf = \chr($childTag);
-        $buf .= pack('N', \count($list));
-        foreach ($list as $value) {
-            [$tag, $payload] = self::detectTag($value);
-            if ($tag !== $childTag) {
-                throw new RuntimeException('NBT list contains mixed tag types');
-            }
-            $buf .= $payload;
-        }
-        return [self::TAG_LIST, $buf];
-    }
-
-    private static function writeString(string $v): string
-    {
-        return pack('n', \strlen($v)) . $v;
-    }
-
-    private static function writeInt(int $v): string
-    {
-        return pack('N', $v);
-    }
-
-    private static function writeLong(int $v): string
-    {
-        $hi = $v >> 32 & 0xffffffff;
-        $lo = $v & 0xffffffff;
-        return pack('N2', $hi, $lo);
-    }
-
-    private static function writeFloat(float $v): string
-    {
-        return pack('G', $v);
-    }
-
-    private static function writeDouble(float $v): string
-    {
-        $data = pack('d', $v);
-        if (self::isLittleEndian()) {
-            $data = strrev($data);
-        }
-        return $data;
-    }
-
-    /**
-     * @return array{0: int, 1: string, 2: mixed}
-     */
-    private static function readNamedTag(string $buf, int &$o): array
-    {
-        $tag = self::readByte($buf, $o);
-        if ($tag === self::TAG_END) {
-            return [self::TAG_END, '', null];
-        }
-        $name = self::readString($buf, $o);
-        $payload = self::readPayload($tag, $buf, $o);
-        return [$tag, $name, $payload];
-    }
-
-    private static function readPayload(int $tag, string $buf, int &$o): mixed
-    {
-        return match ($tag) {
-            self::TAG_BYTE => self::readByte($buf, $o),
-            self::TAG_SHORT => self::readShort($buf, $o),
-            self::TAG_INT => self::readInt($buf, $o),
-            self::TAG_LONG => self::readLong($buf, $o),
-            self::TAG_FLOAT => self::readFloat($buf, $o),
-            self::TAG_DOUBLE => self::readDouble($buf, $o),
-            self::TAG_BYTE_ARRAY => self::readByteArray($buf, $o),
-            self::TAG_STRING => self::readString($buf, $o),
-            self::TAG_LIST => self::readList($buf, $o),
-            self::TAG_COMPOUND => self::readCompound($buf, $o),
-            self::TAG_INT_ARRAY => self::readIntArray($buf, $o),
-            self::TAG_LONG_ARRAY => self::readLongArray($buf, $o),
-            default => throw new RuntimeException('Unsupported NBT payload type: ' . $tag),
-        };
-    }
-
-    private static function readString(string $buf, int &$o): string
-    {
-        $length = self::readShort($buf, $o);
-        self::ensure($buf, $o, $length);
-        $value = substr($buf, $o, $length);
-        $o += $length;
-        return $value;
-    }
-
-    private static function readByte(string $buf, int &$o): int
-    {
-        self::ensure($buf, $o, 1);
-        return \ord($buf[$o++]);
-    }
-
-    private static function readShort(string $buf, int &$o): int
-    {
-        self::ensure($buf, $o, 2);
-        $value = unpack('n', substr($buf, $o, 2));
-        if ($value === false) {
-            throw new RuntimeException('Failed to unpack short');
-        }
-        $o += 2;
-        return $value[1];
-    }
-
-    private static function readInt(string $buf, int &$o): int
-    {
-        self::ensure($buf, $o, 4);
-        $value = unpack('N', substr($buf, $o, 4));
-        if ($value === false) {
-            throw new RuntimeException('Failed to unpack int');
-        }
-        $o += 4;
-        return $value[1];
-    }
-
-    private static function readLong(string $buf, int &$o): int
-    {
-        self::ensure($buf, $o, 8);
-        $value = unpack('N2', substr($buf, $o, 8));
-        if ($value === false) {
-            throw new RuntimeException('Failed to unpack long');
-        }
-        $o += 8;
-        return $value[1] << 32 | $value[2];
-    }
-
-    private static function readFloat(string $buf, int &$o): float
-    {
-        self::ensure($buf, $o, 4);
-        $value = unpack('G', substr($buf, $o, 4));
-        if ($value === false) {
-            throw new RuntimeException('Failed to unpack float');
-        }
-        $o += 4;
-        return $value[1];
-    }
-
-    private static function readDouble(string $buf, int &$o): float
-    {
-        self::ensure($buf, $o, 8);
-        $data = substr($buf, $o, 8);
-        if (self::isLittleEndian()) {
-            $data = strrev($data);
-        }
-        $value = unpack('d', $data);
-        if ($value === false) {
-            throw new RuntimeException('Failed to unpack double');
-        }
-        $o += 8;
-        return $value[1];
-    }
-
-    /**
-     * @return array<int, int>
-     */
-    private static function readByteArray(string $buf, int &$o): array
-    {
-        $length = self::readInt($buf, $o);
-        self::ensure($buf, $o, $length);
-        $data = substr($buf, $o, $length);
-        $o += $length;
-        return array_map('ord', str_split($data));
-    }
-
-    /**
-     * @return array<int, int>
-     */
-    private static function readIntArray(string $buf, int &$o): array
-    {
-        $length = self::readInt($buf, $o);
-        $result = [];
-        for ($i = 0; $i < $length; $i++) {
-            $result[] = self::readInt($buf, $o);
-        }
-        return $result;
-    }
-
-    /**
-     * @return array<int, int>
-     */
-    private static function readLongArray(string $buf, int &$o): array
-    {
-        $length = self::readInt($buf, $o);
-        $result = [];
-        for ($i = 0; $i < $length; $i++) {
-            $result[] = self::readLong($buf, $o);
-        }
-        return $result;
-    }
-
-    /**
-     * @return array<int, mixed>
-     */
-    private static function readList(string $buf, int &$o): array
-    {
-        $childTag = self::readByte($buf, $o);
-        $length = self::readInt($buf, $o);
-        $result = [];
-        for ($i = 0; $i < $length; $i++) {
-            if ($childTag === self::TAG_COMPOUND) {
-                $result[] = self::readCompound($buf, $o);
-            } else {
-                $result[] = self::readPayload($childTag, $buf, $o);
-            }
-        }
-        return $result;
-    }
-
-    /**
-     * @return array<string, mixed>
-     */
-    private static function readCompound(string $buf, int &$o): array
-    {
-        $result = [];
-        while (true) {
-            $tag = self::readByte($buf, $o);
-            if ($tag === self::TAG_END) {
-                break;
-            }
-            $name = self::readString($buf, $o);
-            $result[$name] = self::readPayload($tag, $buf, $o);
-        }
-        return $result;
-    }
-
-    private static function ensure(string $buf, int $offset, int $length): void
-    {
-        if (\strlen($buf) < $offset + $length) {
-            throw new RuntimeException('NBT buffer underrun');
-        }
-    }
-
-    private static function isLittleEndian(): bool
-    {
-        return pack('S', 1) === "\x01\x00";
-    }
-
-    /**
-     * @param array<mixed> $arr
-     */
-    private static function isList(array $arr): bool
-    {
-        return array_keys($arr) === range(0, \count($arr) - 1);
-    }
-}
+	public const TAG_END = 0;
+	public const TAG_BYTE = 1;
+	public const TAG_SHORT = 2;
+	public const TAG_INT = 3;
+	public const TAG_LONG = 4;
+	public const TAG_FLOAT = 5;
+	public const TAG_DOUBLE = 6;
+	public const TAG_BYTE_ARRAY = 7;
+	public const TAG_STRING = 8;
+	public const TAG_LIST = 9;
+	public const TAG_COMPOUND = 10;
+	public const TAG_INT_ARRAY = 11;
+
+	public const FORMAT_BIG_ENDIAN = 0;
+	public const FORMAT_LITTLE_ENDIAN = 1;
+	public const FORMAT_NETWORK = 2;
+
+	/**
+	 * @param array<int|string, mixed> $data
+	 */
+	public static function compound(array $data, string $name = ''): string
+	{
+		return self::writeRoot($data, $name, self::FORMAT_BIG_ENDIAN);
+	}
+
+	/**
+	 * @param array<int|string, mixed> $data
+	 */
+	public static function compoundLittle(array $data, string $name = ''): string
+	{
+		return self::writeRoot($data, $name, self::FORMAT_LITTLE_ENDIAN);
+	}
+
+	/**
+	 * @param array<int|string, mixed> $data
+	 */
+	public static function compoundNetwork(array $data, string $name = ''): string
+	{
+		return self::writeRoot($data, $name, self::FORMAT_NETWORK);
+	}
+
+	public static function parse(string $data, int &$offset = 0): array
+	{
+		return self::parseInternal($data, self::FORMAT_BIG_ENDIAN, $offset);
+	}
+
+	public static function parseLittle(string $data, int &$offset = 0): array
+	{
+		return self::parseInternal($data, self::FORMAT_LITTLE_ENDIAN, $offset);
+	}
+
+	public static function parseNetwork(string $data, int &$offset = 0): array
+	{
+		return self::parseInternal($data, self::FORMAT_NETWORK, $offset);
+	}
+
+	private static function writeRoot(array $data, string $name, int $format): string
+	{
+		return \chr(self::TAG_COMPOUND)
+			. self::writeString($name, $format)
+			. self::writeCompoundPayload($data, $format);
+	}
+
+	private static function parseInternal(string $data, int $format, int &$offset = 0): array
+	{
+		[$tag, $name, $value] = self::readNamedTag($data, $offset, $format);
+
+		if ($tag !== self::TAG_COMPOUND) {
+			throw new RuntimeException('NBT root tag must be a compound, got ' . $tag);
+		}
+
+		if (!\is_array($value)) {
+			throw new RuntimeException('NBT root value must be a compound');
+		}
+
+		return $value;
+	}
+
+	// --- EXPLICIT TAG WRAPPERS ---
+
+	public static function tagByte(int $value): array { return ['__nbt_type' => self::TAG_BYTE, '__nbt_value' => $value]; }
+	public static function tagShort(int $value): array { return ['__nbt_type' => self::TAG_SHORT, '__nbt_value' => $value]; }
+	public static function tagInt(int $value): array { return ['__nbt_type' => self::TAG_INT, '__nbt_value' => $value]; }
+	public static function tagLong(int $value): array { return ['__nbt_type' => self::TAG_LONG, '__nbt_value' => $value]; }
+	public static function tagFloat(float $value): array { return ['__nbt_type' => self::TAG_FLOAT, '__nbt_value' => $value]; }
+	public static function tagDouble(float $value): array { return ['__nbt_type' => self::TAG_DOUBLE, '__nbt_value' => $value]; }
+	public static function tagByteArray(array $value): array { return ['__nbt_type' => self::TAG_BYTE_ARRAY, '__nbt_value' => $value]; }
+	public static function tagString(string $value): array { return ['__nbt_type' => self::TAG_STRING, '__nbt_value' => $value]; }
+	public static function tagIntArray(array $value): array { return ['__nbt_type' => self::TAG_INT_ARRAY, '__nbt_value' => $value]; }
+
+	public static function tagCompound(array $value): array { return ['__nbt_type' => self::TAG_COMPOUND, '__nbt_value' => $value]; }
+	public static function tagList(array $value): array { return ['__nbt_type' => self::TAG_LIST, '__nbt_value' => $value]; }
+
+	private static function detectTag(mixed $value, int $format): array
+	{
+		if (\is_array($value) && isset($value['__nbt_type'], $value['__nbt_value'])) {
+			$type = $value['__nbt_type'];
+			$data = $value['__nbt_value'];
+
+			return match ($type) {
+				self::TAG_BYTE => [self::TAG_BYTE, self::writeByte($data)],
+				self::TAG_SHORT => [self::TAG_SHORT, self::writeShort($data, $format)],
+				self::TAG_INT => [self::TAG_INT, self::writeInt($data, $format)],
+				self::TAG_LONG => [self::TAG_LONG, self::writeLong($data, $format)],
+				self::TAG_FLOAT => [self::TAG_FLOAT, self::writeFloat($data, $format)],
+				self::TAG_DOUBLE => [self::TAG_DOUBLE, self::writeDouble($data, $format)],
+				self::TAG_BYTE_ARRAY => [self::TAG_BYTE_ARRAY, self::writeByteArray($data, $format)],
+				self::TAG_STRING => [self::TAG_STRING, self::writeString($data, $format)],
+				self::TAG_LIST => self::writeList($data, $format),
+				self::TAG_COMPOUND => [self::TAG_COMPOUND, self::writeCompoundPayload($data, $format)],
+				self::TAG_INT_ARRAY => [self::TAG_INT_ARRAY, self::writeIntArray($data, $format)],
+				default => throw new RuntimeException('Unsupported explicit NBT tag: ' . $type),
+			};
+		}
+
+		if (\is_bool($value)) {
+			return [self::TAG_BYTE, self::writeByte($value ? 1 : 0)];
+		}
+
+		if (\is_int($value)) {
+			if ($value >= (-0x7fffffff - 1) && $value <= 0x7fffffff) {
+				return [self::TAG_INT, self::writeInt($value, $format)];
+			}
+			return [self::TAG_LONG, self::writeLong($value, $format)];
+		}
+
+		if (\is_float($value)) {
+			return [self::TAG_DOUBLE, self::writeDouble($value, $format)];
+		}
+
+		if (\is_string($value)) {
+			return [self::TAG_STRING, self::writeString($value, $format)];
+		}
+
+		if (\is_array($value)) {
+			if (self::isList($value)) {
+				return self::writeList($value, $format);
+			}
+			return [self::TAG_COMPOUND, self::writeCompoundPayload($value, $format)];
+		}
+
+		throw new RuntimeException('Unsupported NBT value type: ' . \get_debug_type($value));
+	}
+
+	private static function writeCompoundPayload(array $data, int $format): string
+	{
+		$buf = '';
+		foreach ($data as $name => $value) {
+			[$tag, $payload] = self::detectTag($value, $format);
+			$buf .= \chr($tag) . self::writeString((string) $name, $format) . $payload;
+		}
+		return $buf . \chr(self::TAG_END);
+	}
+
+	private static function writeList(array $list, int $format): array
+	{
+		if ($list === []) {
+			return [self::TAG_LIST, \chr(self::TAG_END) . self::writeInt(0, $format)];
+		}
+
+		[$childTag] = self::detectTag($list[0], $format);
+		if ($childTag === self::TAG_END) throw new RuntimeException('NBT list cannot contain TAG_End');
+
+		$buf = \chr($childTag) . self::writeInt(\count($list), $format);
+		foreach ($list as $value) {
+			[$tag, $payload] = self::detectTag($value, $format);
+			if ($tag !== $childTag) throw new RuntimeException('NBT list contains mixed tag types');
+			$buf .= $payload;
+		}
+
+		return [self::TAG_LIST, $buf];
+	}
+
+	// --- WRITING METHODS ---
+
+	private static function writeString(string $value, int $format): string
+	{
+		$length = \strlen($value);
+		if ($length > 32767) throw new RuntimeException('StringTag cannot hold more than 32767 bytes');
+		if ($format === self::FORMAT_NETWORK) {
+			return self::writeUnsignedVarInt($length) . $value;
+		}
+		return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'v' : 'n', $length) . $value;
+	}
+
+	private static function writeByte(int $value): string { return \chr($value & 0xff); }
+	
+	private static function writeShort(int $value, int $format): string { 
+		return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'v' : 'n', $value); 
+	}
+	
+	private static function writeInt(int $value, int $format): string { 
+		if ($format === self::FORMAT_NETWORK) return self::writeVarInt($value);
+		return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'V' : 'N', $value); 
+	}
+	
+	private static function writeLong(int $value, int $format): string { 
+		if ($format === self::FORMAT_NETWORK) return self::writeVarLong($value);
+		return \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'P' : 'J', $value); 
+	}
+	
+	private static function writeFloat(float $value, int $format): string { 
+		return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'g' : 'G', $value); 
+	}
+	
+	private static function writeDouble(float $value, int $format): string { 
+		return \pack($format !== self::FORMAT_BIG_ENDIAN ? 'e' : 'E', $value); 
+	}
+
+	private static function writeByteArray(array $value, int $format): string
+	{
+		$buf = self::writeInt(\count($value), $format);
+		if ($value !== []) $buf .= \pack('c*', ...$value);
+		return $buf;
+	}
+
+	private static function writeIntArray(array $value, int $format): string
+	{
+		$buf = self::writeInt(\count($value), $format);
+		if ($value !== []) {
+			if ($format === self::FORMAT_NETWORK) {
+				foreach ($value as $v) $buf .= self::writeVarInt($v);
+			} else {
+				$buf .= \pack($format === self::FORMAT_LITTLE_ENDIAN ? 'V*' : 'N*', ...$value);
+			}
+		}
+		return $buf;
+	}
+
+	// --- READING METHODS ---
+
+	private static function readNamedTag(string $buffer, int &$offset, int $format): array
+	{
+		$tag = self::readUnsignedByte($buffer, $offset);
+		if ($tag === self::TAG_END) return [self::TAG_END, '', null];
+
+		$name = self::readString($buffer, $offset, $format);
+		$value = self::readPayload($tag, $buffer, $offset, $format);
+		return [$tag, $name, $value];
+	}
+
+	private static function readPayload(int $tag, string $buffer, int &$offset, int $format): mixed
+	{
+		return match ($tag) {
+			self::TAG_BYTE => self::readByte($buffer, $offset),
+			self::TAG_SHORT => self::readShort($buffer, $offset, $format),
+			self::TAG_INT => self::readInt($buffer, $offset, $format),
+			self::TAG_LONG => self::readLong($buffer, $offset, $format),
+			self::TAG_FLOAT => self::readFloat($buffer, $offset, $format),
+			self::TAG_DOUBLE => self::readDouble($buffer, $offset, $format),
+			self::TAG_BYTE_ARRAY => self::readByteArray($buffer, $offset, $format),
+			self::TAG_STRING => self::readString($buffer, $offset, $format),
+			self::TAG_LIST => self::readList($buffer, $offset, $format),
+			self::TAG_COMPOUND => self::readCompound($buffer, $offset, $format),
+			self::TAG_INT_ARRAY => self::readIntArray($buffer, $offset, $format),
+			default => throw new RuntimeException('Unsupported NBT payload type: ' . $tag . ' (0x' . \dechex($tag) . ') at offset ' . $offset),
+		};
+	}
+
+	private static function signByte(int $value): int { return $value << 56 >> 56; }
+	private static function signShort(int $value): int { return $value << 48 >> 48; }
+	private static function signInt(int $value): int { return $value << 32 >> 32; }
+
+	private static function readString(string $buffer, int &$offset, int $format): string
+	{
+		if ($format === self::FORMAT_NETWORK) {
+			$length = self::readUnsignedVarInt($buffer, $offset);
+		} else {
+			self::ensure($buffer, $offset, 2);
+			$bytes = \substr($buffer, $offset, 2);
+			$offset += 2;
+			$length = \unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'v' : 'n', $bytes)[1];
+		}
+
+		if ($length > 32767) throw new RuntimeException('StringTag cannot hold more than 32767 bytes, got ' . $length);
+		self::ensure($buffer, $offset, $length);
+		$value = \substr($buffer, $offset, $length);
+		$offset += $length;
+		return $value;
+	}
+
+	private static function readUnsignedByte(string $buffer, int &$offset): int {
+		self::ensure($buffer, $offset, 1);
+		return \ord($buffer[$offset++]);
+	}
+
+	private static function readByte(string $buffer, int &$offset): int {
+		self::ensure($buffer, $offset, 1);
+		return self::signByte(\ord($buffer[$offset++]));
+	}
+
+	private static function readShort(string $buffer, int &$offset, int $format): int {
+		self::ensure($buffer, $offset, 2);
+		$bytes = \substr($buffer, $offset, 2);
+		$offset += 2;
+		return self::signShort(\unpack($format !== self::FORMAT_BIG_ENDIAN ? 'v' : 'n', $bytes)[1]);
+	}
+
+	private static function readInt(string $buffer, int &$offset, int $format): int {
+		if ($format === self::FORMAT_NETWORK) return self::readVarInt($buffer, $offset);
+		
+		self::ensure($buffer, $offset, 4);
+		$bytes = \substr($buffer, $offset, 4);
+		$offset += 4;
+		return self::signInt(\unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'V' : 'N', $bytes)[1]);
+	}
+
+	private static function readLong(string $buffer, int &$offset, int $format): int {
+		if ($format === self::FORMAT_NETWORK) return self::readVarLong($buffer, $offset);
+		
+		self::ensure($buffer, $offset, 8);
+		$bytes = \substr($buffer, $offset, 8);
+		$offset += 8;
+		return \unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'P' : 'J', $bytes)[1];
+	}
+
+	private static function readFloat(string $buffer, int &$offset, int $format): float {
+		self::ensure($buffer, $offset, 4);
+		$bytes = \substr($buffer, $offset, 4);
+		$offset += 4;
+		return \unpack($format !== self::FORMAT_BIG_ENDIAN ? 'g' : 'G', $bytes)[1];
+	}
+
+	private static function readDouble(string $buffer, int &$offset, int $format): float {
+		self::ensure($buffer, $offset, 8);
+		$bytes = \substr($buffer, $offset, 8);
+		$offset += 8;
+		return \unpack($format !== self::FORMAT_BIG_ENDIAN ? 'e' : 'E', $bytes)[1];
+	}
+
+	private static function readByteArray(string $buffer, int &$offset, int $format): array {
+		$length = self::readInt($buffer, $offset, $format);
+		if ($length < 0) throw new RuntimeException('Negative NBT byte array length');
+		if ($length === 0) return [];
+		
+		self::ensure($buffer, $offset, $length);
+		$bytes = \substr($buffer, $offset, $length);
+		$offset += $length;
+		return \array_values(\unpack('c*', $bytes) ?: []);
+	}
+
+	private static function readIntArray(string $buffer, int &$offset, int $format): array {
+		$length = self::readInt($buffer, $offset, $format);
+		if ($length < 0) throw new RuntimeException('Negative NBT int array length');
+		if ($length === 0) return [];
+
+		if ($format === self::FORMAT_NETWORK) {
+			$result = [];
+			for ($i = 0; $i < $length; ++$i) $result[] = self::readVarInt($buffer, $offset);
+			return $result;
+		}
+
+		$bytesLength = $length * 4;
+		self::ensure($buffer, $offset, $bytesLength);
+		$bytes = \substr($buffer, $offset, $bytesLength);
+		$offset += $bytesLength;
+		return \array_values(\unpack($format === self::FORMAT_LITTLE_ENDIAN ? 'V*' : 'N*', $bytes) ?: []);
+	}
+
+	private static function readList(string $buffer, int &$offset, int $format): array {
+		$childTag = self::readUnsignedByte($buffer, $offset);
+		$length = self::readInt($buffer, $offset, $format);
+
+		if ($length < 0) throw new RuntimeException('Negative NBT list length');
+		if ($length > 0 && $childTag === self::TAG_END) throw new RuntimeException('Unexpected non-empty list of TAG_End');
+		if ($length === 0) return [];
+
+		$result = [];
+		for ($i = 0; $i < $length; ++$i) {
+			$result[] = self::readPayload($childTag, $buffer, $offset, $format);
+		}
+		return $result;
+	}
+
+	private static function readCompound(string $buffer, int &$offset, int $format): array {
+		$result = [];
+		while (true) {
+			$tagOffset = $offset;
+			$tag = self::readUnsignedByte($buffer, $offset);
+			if ($tag === self::TAG_END) break;
+
+			if ($tag < self::TAG_BYTE || $tag > self::TAG_INT_ARRAY) {
+				throw new RuntimeException('Invalid NBT tag type: ' . $tag . ' (0x' . \dechex($tag) . ') at offset ' . $tagOffset);
+			}
+
+			$name = self::readString($buffer, $offset, $format);
+			$result[$name] = self::readPayload($tag, $buffer, $offset, $format);
+		}
+		return $result;
+	}
+
+	private static function ensure(string $buffer, int $offset, int $length): void {
+		if ($length < 0) throw new RuntimeException('Negative NBT length');
+		$size = \strlen($buffer);
+		if ($offset < 0 || $offset > $size || $length > $size - $offset) {
+			throw new RuntimeException('NBT buffer underrun at offset ' . $offset . ', requested ' . $length . ' bytes, buffer size ' . $size);
+		}
+	}
+
+	private static function isList(array $array): bool {
+		return $array === [] || \array_keys($array) === \range(0, \count($array) - 1);
+	}
+
+	// --- VARINT IMPLEMENTATION ---
+
+	private static function readUnsignedVarInt(string $buffer, int &$offset): int {
+		$value = 0;
+		for ($i = 0; $i <= 28; $i += 7) {
+			if (!isset($buffer[$offset])) throw new RuntimeException("No bytes left in buffer");
+			$b = \ord($buffer[$offset++]);
+			$value |= (($b & 0x7f) << $i);
+			if (($b & 0x80) === 0) return $value;
+		}
+		throw new RuntimeException("VarInt did not terminate after 5 bytes!");
+	}
+
+	private static function readVarInt(string $buffer, int &$offset): int {
+		$raw = self::readUnsignedVarInt($buffer, $offset);
+		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
+		return $temp ^ ($raw & (1 << 63));
+	}
+
+	private static function readUnsignedVarLong(string $buffer, int &$offset): int {
+		$value = 0;
+		for ($i = 0; $i <= 63; $i += 7) {
+			if (!isset($buffer[$offset])) throw new RuntimeException("No bytes left in buffer");
+			$b = \ord($buffer[$offset++]);
+			$value |= (($b & 0x7f) << $i);
+			if (($b & 0x80) === 0) return $value;
+		}
+		throw new RuntimeException("VarLong did not terminate after 10 bytes!");
+	}
+
+	private static function readVarLong(string $buffer, int &$offset): int {
+		$raw = self::readUnsignedVarLong($buffer, $offset);
+		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
+		return $temp ^ ($raw & (1 << 63));
+	}
+
+	private static function writeUnsignedVarInt(int $value): string {
+		$buf = "";
+		$remaining = $value & 0xffffffff;
+		for ($i = 0; $i < 5; ++$i) {
+			$bits = $remaining & 0x7f;
+			if (($remaining >> 7) !== 0) {
+				$buf .= \chr($bits | 0x80);
+			} else {
+				$buf .= \chr($bits & 0x7f);
+				return $buf;
+			}
+			$remaining = (($remaining >> 7) & (\PHP_INT_MAX >> 6));
+		}
+		throw new RuntimeException("Value too large to be encoded as a VarInt");
+	}
+
+	private static function writeVarInt(int $v): string {
+		$v = ($v << 32 >> 32);
+		return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
+	}
+
+	private static function writeVarLong(int $v): string {
+		return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
+	}
+
+	private static function writeUnsignedVarLong(int $value): string {
+		$buf = "";
+		$remaining = $value;
+		for ($i = 0; $i < 10; ++$i) {
+			$bits = $remaining & 0x7f;
+			if (($remaining >> 7) !== 0) {
+				$buf .= \chr($bits | 0x80);
+			} else {
+				$buf .= \chr($bits & 0x7f);
+				return $buf;
+			}
+			$remaining = (($remaining >> 7) & (\PHP_INT_MAX >> 6));
+		}
+		throw new RuntimeException("Value too large to be encoded as a VarLong");
+	}
+}
\ No newline at end of file
diff --git a/src/player/Player.php b/src/player/Player.php
index 52d5855..2c115af 100644
--- a/src/player/Player.php
+++ b/src/player/Player.php
@@ -110,16 +110,6 @@ final class Player extends Entity
         return $this->runtimeId;
     }
 
-    public function getPosition(): \watermossmc\util\Location
-    {
-        return new \watermossmc\util\Location($this->server->getWorld(), $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
-    }
-
-    public function getLocation(): Location
-    {
-        return parent::getLocation();
-    }
-
     public function teleport(float $x, float $y, float $z, ?float $yaw = null, ?float $pitch = null): void
     {
         $this->setPosition($x, $y, $z);
diff --git a/src/world/Chunk.php b/src/world/Chunk.php
index c45fc30..d96e056 100644
--- a/src/world/Chunk.php
+++ b/src/world/Chunk.php
@@ -23,6 +23,7 @@ declare(strict_types=1);
 namespace watermossmc\world;
 
 use watermossmc\binary\Binary;
+use watermossmc\block\BlockRuntimeIdConverter;
 
 final class Chunk
 {
@@ -66,14 +67,18 @@ final class Chunk
     /**
      * Encode chunk to network payload
      */
-    public function encode(): string
+    public function encode(
+		BlockRuntimeIdConverter $converter
+	): string
     {
         $payload = '';
 
         ksort($this->subChunks);
 
         foreach ($this->subChunks as $subChunk) {
-            $payload .= $subChunk->encode();
+            $payload .= $subChunk->encode(
+				$converter
+			);
         }
 
         return $payload;
diff --git a/src/world/SubChunk.php b/src/world/SubChunk.php
index c24fc0d..f6412c7 100644
--- a/src/world/SubChunk.php
+++ b/src/world/SubChunk.php
@@ -24,6 +24,8 @@ namespace watermossmc\world;
 
 use RuntimeException;
 use watermossmc\binary\Binary;
+use watermossmc\block\BlockRegistry;
+use watermossmc\block\BlockRuntimeIdConverter;
 
 final class SubChunk
 {
@@ -49,19 +51,41 @@ final class SubChunk
         return $this->blocks[$index] ?? 0;
     }
 
-    public function encode(): string
-    {
-        $out = Binary::writeByte(8);
-        $palette = array_values(array_unique($this->blocks));
-        $bits = max(1, (int) ceil(log(\count($palette), 2)));
-        $out .= Binary::writeByte($bits);
-        $out .= $this->encodeBlocks($palette, $bits);
-        $out .= Binary::writeVarInt(\count($palette));
-        foreach ($palette as $id) {
-            $out .= Binary::writeVarInt($id);
-        }
-        return $out;
-    }
+    public function encode(
+		BlockRuntimeIdConverter $converter
+	): string {
+		$paletteData = $this->createPalette($converter);
+
+		$palette = $paletteData['palette'];
+		$indexes = $paletteData['indexes'];
+
+		$bits = max(
+			1,
+			(int) ceil(log(count($palette), 2))
+		);
+
+		$out = '';
+
+		$out .= Binary::writeByte(1);
+		$out .= Binary::writeByte($bits);
+
+		$out .= $this->encodeIndexes(
+			$indexes,
+			$bits
+		);
+
+		$out .= Binary::writeVarInt(
+			count($palette)
+		);
+
+		foreach ($palette as $runtimeId) {
+			$out .= Binary::writeUnsignedVarInt(
+				$runtimeId
+			);
+		}
+
+		return $out;
+	}
 
     /**
      * @param array<int, int> $palette
@@ -105,4 +129,91 @@ final class SubChunk
         $subChunk->blocks = array_values($values);
         return $subChunk;
     }
+
+	/**
+	 * @return array<int, int>
+	 */
+	public function getBlocks(): array
+	{
+		return $this->blocks;
+	}
+
+	/**
+	 * @return array{
+	 *     palette: int[],
+	 *     indexes: int[]
+	 * }
+ 	 */
+	public function createPalette(
+		BlockRuntimeIdConverter $converter
+	): array {
+		$palette = [];
+		$paletteMap = [];
+		$indexes = [];
+
+		foreach ($this->blocks as $index => $blockId) {
+			$block = BlockRegistry::get($blockId);
+
+			if ($block === null) {
+				throw new RuntimeException(
+					"Unknown block ID: {$blockId}"
+				);
+			}
+
+			$runtimeId = $converter->toRuntimeId($block);
+
+			if (!isset($paletteMap[$runtimeId])) {
+				$paletteMap[$runtimeId] = count($palette);
+				$palette[] = $runtimeId;
+			}
+
+			$indexes[$index] = $paletteMap[$runtimeId];
+		}
+
+		return [
+			'palette' => $palette,
+			'indexes' => $indexes,
+		];
+	}
+
+	/**
+ 	 * @param int[] $indexes
+ 	 */
+	private function encodeIndexes(
+		array $indexes,
+		int $bits
+	): string {
+		$valuesPerWord = intdiv(32, $bits);
+
+		if ($valuesPerWord <= 0) {
+			throw new RuntimeException(
+				"Invalid bits per block: {$bits}"
+			);
+		}
+
+		$out = '';
+
+		$count = count($indexes);
+		$words = (int) ceil(
+			$count / $valuesPerWord
+		);
+
+		for ($wordIndex = 0; $wordIndex < $words; $wordIndex++) {
+			$value = 0;
+
+			for ($i = 0; $i < $valuesPerWord; $i++) {
+				$index = $wordIndex * $valuesPerWord + $i;
+
+				if ($index >= $count) {
+					break;
+				}
+
+				$value |= $indexes[$index] << ($i * $bits);
+			}
+
+			$out .= Binary::writeInt($value);
+		}
+
+		return $out;
+	}
 }
