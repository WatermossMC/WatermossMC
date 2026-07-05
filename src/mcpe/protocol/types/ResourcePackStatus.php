<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol\types;

final class ResourcePackStatus
{
    public const REFUSED = 1;
    public const SEND_PACKS = 2;
    public const HAVE_ALL_PACKS = 3;
    public const COMPLETED = 4;
}
