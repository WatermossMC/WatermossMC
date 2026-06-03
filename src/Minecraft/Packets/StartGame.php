<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\NBT\NBT;
use WatermossMC\Minecraft\Packets\Types\Experiments;
use WatermossMC\Network\Session;
use WatermossMC\Util\Config;

final class StartGame extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $worldName = Config::getString('level_name', 'PHP World');
        $seed = Config::getInt('level_seed', 12345);
        $gameModeId = 0;

        $position = $s->getPosition();
        $rotation = $s->getRotation();

        $payload = '';
        $payload .= Binary::writeVarLong(1); // actorUniqueId
        $payload .= Binary::writeVarLong($s->getRuntimeId()); // actorRuntimeId
        $payload .= Binary::writeVarInt($gameModeId);

        $payload .= Binary::writeFloat($position['x']);
        $payload .= Binary::writeFloat($position['y']);
        $payload .= Binary::writeFloat($position['z']);

        $payload .= Binary::writeFloat($rotation['pitch']);
        $payload .= Binary::writeFloat($rotation['yaw']);

        // LevelSettings
        $payload .= Binary::writeLong($seed);
        $payload .= Binary::writeLShort(0); // biome type
        $payload .= Binary::writeString(''); // biome name
        $payload .= Binary::writeVarInt(0); // dimension
        $payload .= Binary::writeVarInt(0); // generator
        $payload .= Binary::writeVarInt($gameModeId);
        $payload .= Binary::writeBool(false); // hardcore
        $payload .= Binary::writeVarInt(1); // difficulty
        $payload .= Binary::writeVarInt(0); // spawn x
        $payload .= Binary::writeVarInt(64); // spawn y
        $payload .= Binary::writeVarInt(0); // spawn z
        $payload .= Binary::writeBool(true); // achievements disabled
        $payload .= Binary::writeVarInt(0); // editor world type
        $payload .= Binary::writeBool(false); // created in editor mode
        $payload .= Binary::writeBool(false); // exported from editor mode
        $payload .= Binary::writeVarInt(0); // time
        $payload .= Binary::writeVarInt(0); // edu edition offer
        $payload .= Binary::writeBool(false); // edu features enabled
        $payload .= Binary::writeString(''); // edu product UUID
        $payload .= Binary::writeFloat(0.0); // rain level
        $payload .= Binary::writeFloat(0.0); // lightning level
        $payload .= Binary::writeBool(false); // platform locked content confirmed
        $payload .= Binary::writeBool(true); // multiplayer game
        $payload .= Binary::writeBool(true); // LAN broadcast
        $payload .= Binary::writeVarInt(0); // xbox live broadcast mode
        $payload .= Binary::writeVarInt(0); // platform broadcast mode
        $payload .= Binary::writeBool(false); // commands enabled
        $payload .= Binary::writeBool(false); // texture packs required
        $payload .= Binary::writeVarInt(0); // game rule count
        $payload .= Experiments::writeEmpty();
        $payload .= Binary::writeBool(false); // bonus chest enabled
        $payload .= Binary::writeBool(false); // start with map enabled
        $payload .= Binary::writeVarInt(0); // default player permission
        $payload .= Binary::writeLInt(4); // server chunk tick radius
        $payload .= Binary::writeBool(false); // locked behavior pack
        $payload .= Binary::writeBool(false); // locked resource pack
        $payload .= Binary::writeBool(false); // from locked world template
        $payload .= Binary::writeBool(false); // use msa gamertags only
        $payload .= Binary::writeBool(false); // from world template
        $payload .= Binary::writeBool(false); // world template option locked
        $payload .= Binary::writeBool(false); // only spawn v1 villagers
        $payload .= Binary::writeBool(false); // disable persona
        $payload .= Binary::writeBool(false); // disable custom skins
        $payload .= Binary::writeBool(false); // mute emote announcements
        $payload .= Binary::writeString('1.21.124');
        $payload .= Binary::writeLInt(0); // limited world width
        $payload .= Binary::writeLInt(0); // limited world length
        $payload .= Binary::writeBool(true); // is new nether
        $payload .= Binary::writeString(''); // education URI button
        $payload .= Binary::writeString(''); // education URI link
        $payload .= Binary::writeBool(false); // experimental gameplay override absent
        $payload .= Binary::writeUInt8(0); // chat restriction level
        $payload .= Binary::writeBool(false); // disable player interactions
        $payload .= Binary::writeString(''); // server identifier
        $payload .= Binary::writeString(''); // scenario identifier
        $payload .= Binary::writeString(''); // world identifier
        $payload .= Binary::writeString(''); // owner identifier

        $payload .= Binary::writeString($worldName); // levelId
        $payload .= Binary::writeString($worldName); // worldName
        $payload .= Binary::writeString(''); // premiumWorldTemplateId
        $payload .= Binary::writeBool(false); // isTrial
        $payload .= Binary::writeVarInt(0); // rewind history size
        $payload .= Binary::writeBool(false); // server authoritative block breaking
        $payload .= Binary::writeLong(0); // current tick
        $payload .= Binary::writeVarInt(0); // enchantment seed

        $payload .= Binary::writeVarInt(0); // block palette count
        $payload .= Binary::writeString(''); // multiplayer correlation id
        $payload .= Binary::writeBool(false); // enable new inventory system
        $payload .= Binary::writeString('WatermossMC'); // server software version
        $payload .= NBT::compound([]); // player actor properties
        $payload .= Binary::writeLong(0); // block palette checksum
        $payload .= Binary::writeUUID('00000000-0000-0000-0000-000000000000'); // worldTemplateId
        $payload .= Binary::writeBool(false); // enable client side chunk generation
        $payload .= Binary::writeBool(false); // block network IDs are hashes
        $payload .= Binary::writeBool(false); // disable client sounds
        $payload .= Binary::writeBool(false); // serverJoinInformation absent

        self::sendBatch(ProtocolInfo::START_GAME_PACKET, $payload, $s, $sock);
    }

    private static function gameModeToId(string $gameMode): int
    {
        return match (strtolower($gameMode)) {
            'creative' => 0,
            'survival' => 1,
            'adventure' => 2,
            'spectator' => 3,
            default => 1,
        };
    }
}
