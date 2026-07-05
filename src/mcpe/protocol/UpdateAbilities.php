<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class UpdateAbilities extends Packet
{
    // playerPermission
    public const PERMISSION_VISITOR = 0;
    public const PERMISSION_MEMBER = 1;
    public const PERMISSION_OPERATOR = 2;
    public const PERMISSION_CUSTOM = 3;

    // commandPermission
    public const CMD_PERMISSION_NORMAL = 0;
    public const CMD_PERMISSION_OPERATOR = 2;
    public const CMD_PERMISSION_HOST = 3;
    public const CMD_PERMISSION_AUTOMATION = 4;
    public const CMD_PERMISSION_ADMIN = 5;

    // AbilitiesLayer IDs
    private const LAYER_CACHE = 0;
    private const LAYER_BASE = 1;
    private const LAYER_SPECTATOR = 2;
    private const LAYER_COMMANDS = 3;
    private const LAYER_EDITOR = 4;
    private const LAYER_LOADING_SCREEN = 5;

    // Ability bit positions
    private const ABILITY_BUILD = 0;
    private const ABILITY_MINE = 1;
    private const ABILITY_DOORS_AND_SWITCHES = 2;
    private const ABILITY_OPEN_CONTAINERS = 3;
    private const ABILITY_ATTACK_PLAYERS = 4;
    private const ABILITY_ATTACK_MOBS = 5;
    private const ABILITY_OPERATOR = 6;
    private const ABILITY_TELEPORT = 7;
    private const ABILITY_INVULNERABLE = 8;
    private const ABILITY_FLYING = 9;
    private const ABILITY_ALLOW_FLIGHT = 10;
    private const ABILITY_INFINITE_RESOURCES = 11;
    private const ABILITY_LIGHTNING = 12;
    private const ABILITY_FLY_SPEED = 13; // float, not bool
    private const ABILITY_WALK_SPEED = 14; // float, not bool
    private const ABILITY_MUTED = 15;
    private const ABILITY_WORLD_BUILDER = 16;
    private const ABILITY_NO_CLIP = 17;
    private const ABILITY_PRIVILEGED_BUILDER = 18;
    private const ABILITY_VERTICAL_FLY_SPEED = 19; // float, not bool

    public static function send(
        Session $s,
        Socket $sock,
        bool $flying = false,
        bool $mayFly = false,
        bool $operator = false
    ): void {
        $playerPermission = self::PERMISSION_MEMBER;
        $commandPermission = $operator
            ? self::CMD_PERMISSION_OPERATOR
            : self::CMD_PERMISSION_NORMAL;

        $payload = '';

        // AbilitiesData header
        // targetActorUniqueId — LE signed long (NOT a varlong!)
        $payload .= Binary::writeLLong($s->getRuntimeId());
        $payload .= Binary::writeUInt8($playerPermission);
        $payload .= Binary::writeUInt8($commandPermission);

        // Layer count
        $payload .= Binary::writeUInt8(1);

        // --- Base layer ---
        // Build the bool-ability bitmasks
        $setAbilities = 0;
        $setAbilityValues = 0;

        $boolAbilities = [
            self::ABILITY_BUILD => true,
            self::ABILITY_MINE => true,
            self::ABILITY_DOORS_AND_SWITCHES => true,
            self::ABILITY_OPEN_CONTAINERS => true,
            self::ABILITY_ATTACK_PLAYERS => true,
            self::ABILITY_ATTACK_MOBS => true,
            self::ABILITY_OPERATOR => $operator,
            self::ABILITY_TELEPORT => $operator,
            self::ABILITY_INVULNERABLE => false,
            self::ABILITY_FLYING => $flying,
            self::ABILITY_ALLOW_FLIGHT => $mayFly,
            self::ABILITY_INFINITE_RESOURCES => false,
            self::ABILITY_LIGHTNING => false,
            self::ABILITY_MUTED => false,
            self::ABILITY_WORLD_BUILDER => false,
            self::ABILITY_NO_CLIP => false,
            self::ABILITY_PRIVILEGED_BUILDER => false,
        ];

        foreach ($boolAbilities as $bit => $value) {
            $setAbilities |= (1 << $bit);
            $setAbilityValues |= ($value ? (1 << $bit) : 0);
        }

        // Float abilities: flySpeed and walkSpeed are set, verticalFlySpeed is not
        $flySpeed = 0.05;
        $verticalFlySpeed = 0.0;  // null = not set → must be 0.0 and bit NOT set
        $walkSpeed = 0.1;

        $setAbilities |= (1 << self::ABILITY_FLY_SPEED);
        $setAbilities |= (1 << self::ABILITY_WALK_SPEED);
        // ABILITY_VERTICAL_FLY_SPEED bit intentionally NOT set (null)

        $payload .= Binary::writeLShort(self::LAYER_BASE); // layerId (LE uint16)
        $payload .= Binary::writeLInt($setAbilities);      // setAbilities (LE uint32)
        $payload .= Binary::writeLInt($setAbilityValues);  // setAbilityValues (LE uint32)
        $payload .= Binary::writeFloat($flySpeed);         // flySpeed (LE float)
        $payload .= Binary::writeFloat($verticalFlySpeed); // verticalFlySpeed (LE float)
        $payload .= Binary::writeFloat($walkSpeed);        // walkSpeed (LE float)

        self::sendBatch(ProtocolInfo::UPDATE_ABILITIES_PACKET, $payload, $s, $sock);
    }
}
