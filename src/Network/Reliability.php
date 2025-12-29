<?php

declare(strict_types=1);

namespace WatermossMC\Network;

final class Reliability
{
    public const UNRELIABLE = 0;
    public const UNRELIABLE_SEQUENCED = 1;
    public const RELIABLE = 2;
    public const RELIABLE_ORDERED = 3;
    public const RELIABLE_SEQUENCED = 4;
    public const UNRELIABLE_WITH_ACK_RECEIPT = 5;
    public const RELIABLE_WITH_ACK_RECEIPT = 6;
    public const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;
}
