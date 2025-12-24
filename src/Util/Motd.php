<?php

declare(strict_types=1);

namespace WatermossMC\Util;

/**
 * Bedrock MOTD builder (SPEC CORRECT)
 *
 * Format:
 * Edition;
 * MOTD line 1;
 * Protocol;
 * Version;
 * Online;
 * Max;
 * ServerId;
 * MOTD line 2;
 * GameMode (string);
 * GameMode (numeric);
 * IPv4 Port;
 * IPv6 Port;
 */
final class Motd
{
    private string $motd = 'WatermossMC';

    private string $worldName = 'Minimal PHP RakNet Server';

    private int $protocol = 860;

    private string $version = '1.21.124';

    private int $onlinePlayers = 0;

    private int $maxPlayers = 20;

    private string $gameMode = 'Survival';

    private int $gameModeId = 1; // Survival

    private int $port = 19132;

    public function motd(string $value): self
    {
        $this->motd = $value;
        return $this;
    }

    public function worldName(string $value): self
    {
        $this->worldName = $value;
        return $this;
    }

    public function protocol(int $value): self
    {
        $this->protocol = $value;
        return $this;
    }

    public function version(string $value): self
    {
        $this->version = $value;
        return $this;
    }

    public function players(int $online, int $max): self
    {
        $this->onlinePlayers = $online;
        $this->maxPlayers = $max;
        return $this;
    }

    public function gameMode(string $name, int $id): self
    {
        $this->gameMode = $name;
        $this->gameModeId = $id;
        return $this;
    }

    public function port(int $value): self
    {
        $this->port = $value;
        return $this;
    }

    public function build(int|string $serverId): string
    {
        return implode(';', [
            'MCPE',
            $this->motd,
            (string) $this->protocol,
            $this->version,
            (string) $this->onlinePlayers,
            (string) $this->maxPlayers,
            (string) $serverId,
            $this->worldName,
            $this->gameMode,
            (string) $this->gameModeId,
            (string) $this->port, // IPv4
            (string) $this->port, // IPv6
        ]);
    }
}
