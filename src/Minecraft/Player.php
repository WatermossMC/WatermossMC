<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft;

use WatermossMC\Network\Session;

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

    /** @var array<string, float|int|bool>|null */
    public ?array $pendingMove = null;

    public function __construct(Session $s, string $username)
    {
        $this->session = $s;
        $this->uuid = $s->getUuid();
        $this->username = $username;
        $this->runtimeId = $s->getRuntimeId();
    }
}
