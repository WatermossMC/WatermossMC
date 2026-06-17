<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

final class ProtocolInfo
{
    public const CURRENT_PROTOCOL = 860;
    public const MINECRAFT_VERSION = 'v1.21.124';
    public const MINECRAFT_VERSION_NETWORK = '1.21.124';

    public const LOGIN_PACKET = 0x01;
    public const PLAY_STATUS_PACKET = 0x02;
    public const SERVER_TO_CLIENT_HANDSHAKE_PACKET = 0x03;
    public const CLIENT_TO_SERVER_HANDSHAKE_PACKET = 0x04;
    public const DISCONNECT_PACKET = 0x05;
    public const RESOURCE_PACKS_INFO_PACKET = 0x06;
    public const RESOURCE_PACK_STACK_PACKET = 0x07;
    public const RESOURCE_PACK_CLIENT_RESPONSE_PACKET = 0x08;
    public const TEXT_PACKET = 0x09;
    public const NETWORK_SETTINGS_PACKET = 0x8F;
    public const SET_TIME_PACKET = 0x0A;
    public const START_GAME_PACKET = 0x0B;
    public const ADD_PLAYER_PACKET = 0x0C;
    public const UPDATE_POSITION_PACKET = 0x15;
    public const LEVEL_CHUNK_PACKET = 0x3A;
    public const PLAYER_LIST_PACKET = 0x3F;
    public const SPAWN_POSITION_PACKET = 0x44;
    public const REQUEST_NETWORK_SETTINGS_PACKET = 0xC1;
    public const UPDATE_ABILITIES_PACKET = 0xBB;

    public const CLIENT_CACHE_STATUS_PACKET = 0x81;
}
