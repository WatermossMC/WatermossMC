<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets\Types;

final class MovePlayerMode
{
    public const NORMAL = 0;
    public const RESET = 1;
    public const TELEPORT = 2;
    public const PITCH = 3;
}
