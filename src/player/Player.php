<?php

declare(strict_types=1);

namespace watermossmc\player;

use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Disconnect;
use watermossmc\mcpe\protocol\Text;

final class Player
{
    public Session $session;

    public string $uuid;

    public string $username;

    public int $runtimeId;

    public float $x = 0;

    public float $y = 64;

    public float $z = 0;

    public float $yaw = 0;

    public float $pitch = 0;

    public float $headYaw = 0;

    public bool $onGround = true;

    private \watermossmc\Server $server;

    /** @var array<string, float|int|bool>|null */
    public ?array $pendingMove = null;

    public function __construct(Session $s, string $username, \watermossmc\Server $server)
    {
        $this->session = $s;
        $this->uuid = $s->getUuid();
        $this->username = $username;
        $this->runtimeId = $s->getRuntimeId();
        $this->server = $server;
    }

    public function getName(): string
    {
        return $this->username;
    }

    public function getUniqueId(): string
    {
        return $this->uuid;
    }

    public function getRuntimeId(): int
    {
        return $this->runtimeId;
    }

    public function getPosition(): \watermossmc\util\Location
    {
        return new \watermossmc\util\Location(
            $this->server->getWorld(),
            $this->x,
            $this->y,
            $this->z,
            $this->yaw,
            $this->pitch
        );
    }

    public function getLocation(): \watermossmc\util\Location
    {
        return $this->getPosition();
    }

    public function teleport(float $x, float $y, float $z, ?float $yaw = null, ?float $pitch = null): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->yaw = $yaw ?? $this->yaw;
        $this->pitch = $pitch ?? $this->pitch;
        $this->session->setPosition($x, $y, $z);
    }

    public function sendMessage(string $message, int $type = Text::TYPE_RAW): bool
    {
        $socket = $this->session->getSocket();
        if ($socket === null || !$this->session->isPlaying()) {
            return false;
        }

        Text::send($this->session, $socket, $message, $type);
        return true;
    }

    public function kick(string $reason = "Disconnected"): bool
    {
        $socket = $this->session->getSocket();
        if ($socket === null) {
            $this->session->close(false);
            return false;
        }

        Disconnect::send($this->session, $socket, $reason);
        $this->session->close(false);
        return true;
    }
}
