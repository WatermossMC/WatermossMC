<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

final class Signal
{
    public const CONNECT_REQUEST = 'CONNECTREQUEST';

    public const CONNECT_RESPONSE = 'CONNECTRESPONSE';

    public const CANDIDATE_ADD = 'CANDIDATEADD';

    public const CONNECT_ERROR = 'CONNECTERROR';

    public const ERROR_NONE = 0;

    public const ERROR_INTERNAL = 7;

    /** The connection ID is a uint64 held as a decimal string. */
    public function __construct(
        public readonly string $type,
        public readonly string $connectionId,
        public readonly string $data = ''
    ) {
    }

    public function encode(): string
    {
        return $this->type . ' ' . $this->connectionId . ' ' . $this->data;
    }

    public static function decode(string $raw): ?self
    {
        $p1 = strpos($raw, ' ');
        if ($p1 === false) {
            return null;
        }
        $type = substr($raw, 0, $p1);
        $p2 = strpos($raw, ' ', $p1 + 1);
        if ($p2 === false) {
            $connectionId = substr($raw, $p1 + 1);
            $data = '';
        } else {
            $connectionId = substr($raw, $p1 + 1, $p2 - $p1 - 1);
            $data = substr($raw, $p2 + 1);
        }
        if ($connectionId === '' || !ctype_digit($connectionId)) {
            return null;
        }
        if (!\in_array($type, [self::CONNECT_REQUEST, self::CONNECT_RESPONSE, self::CANDIDATE_ADD, self::CONNECT_ERROR], true)) {
            return null;
        }
        return new self($type, $connectionId, $data);
    }
}
