<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

use Closure;
use watermossmc\util\Logger;

/**
 * Server-side (passive) SCTP-over-DTLS: INIT/COOKIE handshake, reliable + ordered DATA/SACK,
 * retransmission (timer + fast retransmit), FORWARD-TSN, HEARTBEAT, DCEP (RFC 8832).
 */
final class SctpAssociation
{
    public const PPID_DCEP = 50;

    public const PPID_BINARY = 53;

    private const MAX_PAYLOAD = 1000;
    private const MTU = 1200;
    private const MASK = 0xFFFFFFFF;
    private const RWND = 1_048_576;
    private const MAX_CWND = 1_048_576;

    private const ST_LISTEN = 0;
    private const ST_COOKIE_SENT = 1;
    private const ST_ESTABLISHED = 2;
    private const ST_CLOSED = 3;

    private int $state = self::ST_LISTEN;
    private int $localTag;
    private int $peerTag = 0;
    private int $peerCumTsn = 0;

    private bool $peerSupportsForwardTsn = false;
    private string $cookie = '';
    private int $localInitTsn;
    private int $initOutStreams = 0;
    private int $initInStreams = 0;

    private int $cumRecv = 0;

    /** @var array<int, true> */
    private array $recvAbove = [];

    /** @var array<int, array{flags: int, sid: int, ssn: int, ppid: int, data: string}> */
    private array $inbox = [];

    /** @var int[] */
    private array $dups = [];

    /** @var array<int, int> */
    private array $nextSsn = [];

    /** @var array<int, array<int, array{0: int, 1: string}>> */
    private array $held = [];

    private int $nextTsn;

    /** @var array<int, int> */
    private array $outSsn = [];

    /** @var array<int, array{tsn: int, value: string, size: int, retransmit: bool}> */
    private array $pending = [];

    /** @var array<int, array{pkt: string, size: int, sent: float, tries: int, acked: bool, miss: int, retransmit: bool, abandoned: bool}> */
    private array $outstanding = [];

    private int $flight = 0;
    private int $cwnd = 4 * self::MTU;
    private int $ssthresh = self::MAX_CWND;
    private int $peerRwnd = 65535;
    private float $srtt = -1.0;
    private float $rttvar = 0.0;
    private float $rto = 1.0;

    /** Last INIT / COOKIE-ECHO packet, kept so the T1 timer can retransmit it while the handshake is open. */
    private string $handshakePkt = '';
    private float $handshakeAt = 0.0;
    private int $handshakeTries = 0;

    /** @var array<int, string> */
    private array $channels = [];

    /** @var array<int, bool> */
    private array $channelUnreliable = [];

    /**
     * @param Closure(string): void              $sendPacket    sends an SCTP packet over DTLS
     * @param Closure(int, int, string): void    $onMessage     (streamId, ppid, data) user message
     * @param Closure(int, string): void         $onChannelOpen (streamId, label)
     * @param Closure(string): void              $onClose
     */
    public function __construct(
        private readonly Closure $sendPacket,
        private readonly Closure $onMessage,
        private readonly Closure $onChannelOpen,
        private readonly Closure $onClose,
        private readonly int $localPort = 5000,
        private int $peerPort = 5000
    ) {
        $this->localTag = random_int(1, 0x7FFFFFFF);
        $this->localInitTsn = random_int(1, 0x7FFFFFFF);
        $this->nextTsn = $this->localInitTsn;
        $this->peerCumTsn = ($this->localInitTsn - 1) & self::MASK;
    }

    public function isEstablished(): bool
    {
        return $this->state === self::ST_ESTABLISHED;
    }

    public function getChannelLabel(int $streamId): ?string
    {
        return $this->channels[$streamId] ?? null;
    }

    public function isChannelUnreliable(int $streamId): bool
    {
        return $this->channelUnreliable[$streamId] ?? false;
    }

    public function initiate(): void
    {
        if ($this->state !== self::ST_LISTEN) {
            return;
        }
        $value = pack('NNnnN', $this->localTag, self::RWND, 16, 16, $this->localInitTsn)
            . self::param(0xC000, '');
        $this->state = self::ST_COOKIE_SENT;
        Logger::debug("[NetherNet] SCTP sending INIT | localTag={$this->localTag} | dstPort={$this->peerPort}");
        $this->sendHandshake([[1, 0, $value]]);
    }

    /** Sends a handshake chunk (INIT / COOKIE-ECHO) and arms the retransmission timer for it. */
    private function sendHandshake(array $chunks): void
    {
        $body = '';
        foreach ($chunks as [$type, $flags, $value]) {
            $body .= self::chunk($type, $flags, $value);
        }
        $this->handshakePkt = $this->buildPacket($body, $this->peerTag);
        $this->handshakeAt = microtime(true);
        $this->handshakeTries = 0;
        ($this->sendPacket)($this->handshakePkt);
    }

    public function receive(string $pkt): void
    {
        $n = \strlen($pkt);
        if ($n < 16 || $this->state === self::ST_CLOSED) {
            return;
        }
        $h = unpack('nsrc/ndst/Nvtag/Vsum', $pkt);
        if ($h === false) {
            return;
        }
        $zeroed = substr($pkt, 0, 8) . "\0\0\0\0" . substr($pkt, 12);
        if (Crc32c::compute($zeroed) !== $h['sum']) {
            Logger::debug("[NetherNet] SCTP packet dropped: bad CRC32c (size={$n})");
            return;
        }
        $this->peerPort = $h['src'];
        $needSack = false;
        $off = 12;
        while ($off + 4 <= $n && $this->state !== self::ST_CLOSED) {
            $type = \ord($pkt[$off]);
            $flags = \ord($pkt[$off + 1]);
            $clen = (\ord($pkt[$off + 2]) << 8) | \ord($pkt[$off + 3]);
            if ($clen < 4 || $off + $clen > $n) {
                break;
            }
            $val = substr($pkt, $off + 4, $clen - 4);
            $off += $clen + ((4 - ($clen % 4)) % 4);

            Logger::debug("[NetherNet] SCTP chunk received | type={$type} | state={$this->state}");
            if ($type !== 1 && $type !== 2 && $h['vtag'] !== $this->localTag) {
                Logger::debug("[NetherNet] SCTP chunk type={$type} dropped: vtag {$h['vtag']} != localTag {$this->localTag}");
                continue;
            }
            switch ($type) {
                case 0:
                    if ($this->handleData($flags, $val)) {
                        $needSack = true;
                    }
                    break;
                case 1:
                    $this->handleInit($val);
                    break;
                case 2:
                    $this->handleInitAck($val);
                    break;
                case 3:
                    $this->handleSack($val);
                    break;
                case 4:
                    $this->sendChunks([[5, 0, $val]]);
                    break;
                case 6:
                    $this->closeAssoc('SCTP abort');
                    break;
                case 7:
                    $this->sendChunks([[8, 0, '']]);
                    $this->closeAssoc('SCTP shutdown');
                    break;
                case 10:
                    $this->handleCookieEcho($val);
                    break;
                case 11:
                    $this->handleCookieAck();
                    break;
                case 192:
                    if ($this->handleForwardTsn($val)) {
                        $needSack = true;
                    }
                    break;
            }
        }
        if ($needSack && $this->state === self::ST_ESTABLISHED) {
            $this->sendSack();
        }
    }

    private function handleInit(string $v): void
    {
        if (\strlen($v) < 16 || $this->state === self::ST_ESTABLISHED) {
            return;
        }
        $i = unpack('Ntag/Nrwnd/nos/nis/Ntsn', $v);
        if ($i === false || $i['tag'] === 0) {
            return;
        }
        $this->peerSupportsForwardTsn = $this->hasForwardTsnParameter(substr($v, 16));
        // LISTEN: normal responder. COOKIE_SENT with no cookie yet: INIT collision (we sent our own INIT first),
        // RFC 4960 5.2.1 - answer with an INIT-ACK as well; our own INIT stays pending.
        if ($this->state === self::ST_LISTEN || ($this->state === self::ST_COOKIE_SENT && $this->cookie === '')) {
            $this->peerTag = $i['tag'];
            $this->cumRecv = ($i['tsn'] - 1) & self::MASK;
            $this->peerRwnd = $i['rwnd'];
            $this->initOutStreams = $i['is'];
            $this->initInStreams = $i['os'];
            $this->cookie = random_bytes(16);
            $this->state = self::ST_COOKIE_SENT;
        }
        if ($this->cookie === '') {
            return;
        }
        $value = pack('NNnnN', $this->localTag, self::RWND, $this->initOutStreams, $this->initInStreams, $this->localInitTsn)
            . self::param(7, $this->cookie)
            . self::param(0xC000, '');
        $this->sendChunks([[2, 0, $value]]);
    }

    private function handleInitAck(string $v): void
    {
        if (\strlen($v) < 16 || $this->state !== self::ST_COOKIE_SENT) {
            return;
        }
        $i = unpack('Ntag/Nrwnd/nos/nis/Ntsn', $v);
        if ($i === false || $i['tag'] === 0) {
            return;
        }
        $this->peerTag = $i['tag'];
        $this->cumRecv = ($i['tsn'] - 1) & self::MASK;
        $this->peerRwnd = $i['rwnd'];

        $cookie = $this->extractCookieParameter(substr($v, 16));
        if ($cookie === '') {
            return;
        }

        Logger::debug('[NetherNet] SCTP INIT-ACK received, sending COOKIE-ECHO');
        $this->sendHandshake([[10, 0, $cookie]]);
    }

    private function extractCookieParameter(string $params): string
    {
        $offset = 0;
        $length = strlen($params);

        while ($offset + 4 <= $length) {
            $param = unpack('ntype/nlength', $params, $offset);

            if ($param === false || $param['length'] < 4) {
                return '';
            }

            $paramLength = $param['length'];

            if ($offset + $paramLength > $length) {
                return '';
            }

            if ($param['type'] === 7) {
                return substr($params, $offset + 4, $paramLength - 4);
            }

            $offset += $paramLength + ((4 - ($paramLength % 4)) % 4);
        }

        return '';
    }

    private function handleCookieAck(): void
    {
        if ($this->state === self::ST_COOKIE_SENT) {
            $this->state = self::ST_ESTABLISHED;
            $this->handshakePkt = '';
            Logger::debug('[NetherNet] SCTP association established (COOKIE-ACK)');
            $this->transmitPending(microtime(true));
        }
    }

    private function handleCookieEcho(string $v): void
    {
        if ($this->state === self::ST_COOKIE_SENT && hash_equals($this->cookie, $v)) {
            $this->state = self::ST_ESTABLISHED;
            $this->handshakePkt = '';
            Logger::debug('[NetherNet] SCTP association established (COOKIE-ECHO)');
            $this->sendChunks([[11, 0, '']]);
        } elseif ($this->state === self::ST_ESTABLISHED && hash_equals($this->cookie, $v)) {
            $this->sendChunks([[11, 0, '']]);
        }
    }

    private function handleData(int $flags, string $v): bool
    {
        if ($this->state !== self::ST_ESTABLISHED || \strlen($v) < 16) {
            return false;
        }
        $d = unpack('Ntsn/nsid/nssn/Nppid', $v);
        if ($d === false) {
            return false;
        }
        $tsn = $d['tsn'];
        if ($this->tsnLe($tsn, $this->cumRecv) || isset($this->recvAbove[$tsn])) {
            $this->dups[] = $tsn;
            return true;
        }
        if ((($tsn - $this->cumRecv) & self::MASK) > 65536) {
            return false;
        }
        $this->recvAbove[$tsn] = true;
        $this->inbox[$tsn] = ['flags' => $flags, 'sid' => $d['sid'], 'ssn' => $d['ssn'], 'ppid' => $d['ppid'], 'data' => substr($v, 12)];
        $this->advanceCum();
        $this->assemble($tsn);
        return true;
    }

    private function advanceCum(): void
    {
        while (true) {
            $next = ($this->cumRecv + 1) & self::MASK;
            if (!isset($this->recvAbove[$next])) {
                return;
            }
            $this->cumRecv = $next;
            unset($this->recvAbove[$next]);
        }
    }

    private function assemble(int $tsn): void
    {
        $start = $tsn;
        for ($i = 0;; $i++) {
            $c = $this->inbox[$start] ?? null;
            if ($c === null || $i > 4096) {
                return;
            }
            if (($c['flags'] & 2) !== 0) {
                break;
            }
            $start = ($start - 1) & self::MASK;
        }
        $parts = [];
        $keys = [];
        $t = $start;
        for ($i = 0;; $i++) {
            $c = $this->inbox[$t] ?? null;
            if ($c === null || $i > 4096) {
                return;
            }
            $parts[] = $c['data'];
            $keys[] = $t;
            if (($c['flags'] & 1) !== 0) {
                break;
            }
            $t = ($t + 1) & self::MASK;
        }
        $first = $this->inbox[$start];
        foreach ($keys as $k) {
            unset($this->inbox[$k]);
        }
        $this->completeMessage($first['sid'], $first['ssn'], $first['ppid'], ($first['flags'] & 4) !== 0, implode('', $parts));
    }

    private function completeMessage(int $sid, int $ssn, int $ppid, bool $unordered, string $data): void
    {
        if ($unordered) {
            $this->deliver($sid, $ppid, $data);
            return;
        }
        $next = $this->nextSsn[$sid] ?? 0;
        if ($ssn === $next) {
            $this->deliver($sid, $ppid, $data);
            $next = ($next + 1) & 0xFFFF;
            while (isset($this->held[$sid][$next])) {
                [$p, $d] = $this->held[$sid][$next];
                unset($this->held[$sid][$next]);
                $this->deliver($sid, $p, $d);
                $next = ($next + 1) & 0xFFFF;
            }
            $this->nextSsn[$sid] = $next;
        } elseif ((($ssn - $next) & 0xFFFF) < 0x8000 && \count($this->held[$sid] ?? []) < 1024) {
            $this->held[$sid][$ssn] = [$ppid, $data];
        }
    }

    private function deliver(int $sid, int $ppid, string $data): void
    {
        if ($ppid === self::PPID_DCEP) {
            if ($data !== '' && \ord($data[0]) === 3 && \strlen($data) >= 12) {
                $u = unpack('Ctype/Cch/npri/Nrel/nll/npl', $data);
                if ($u === false) {
                    return;
                }
                $label = substr($data, 12, $u['ll']);
                $this->channels[$sid] = $label;
                $this->channelUnreliable[$sid] = $u['ch'] === 0x81 && $u['rel'] === 0;
                $this->sendMessage($sid, self::PPID_DCEP, "\x02");
                ($this->onChannelOpen)($sid, $label);
            }
            return;
        }
        if ($ppid === 56 || $ppid === 57) {
            $data = '';
        }
        ($this->onMessage)($sid, $ppid, $data);
    }

    private function handleForwardTsn(string $v): bool
    {
        if (\strlen($v) < 4) {
            return false;
        }
        $new = (unpack('N', $v) ?: [1 => 0])[1];
        if ($this->tsnLe($new, $this->cumRecv)) {
            return true;
        }
        $this->cumRecv = $new;
        foreach (array_keys($this->recvAbove) as $t) {
            if ($this->tsnLe($t, $new)) {
                unset($this->recvAbove[$t]);
            }
        }
        foreach (array_keys($this->inbox) as $t) {
            if ($this->tsnLe($t, $new)) {
                unset($this->inbox[$t]);
            }
        }
        for ($o = 4; $o + 4 <= \strlen($v); $o += 4) {
            $s = unpack('nsid/nssn', $v, $o);
            if ($s === false) {
                break;
            }
            $this->nextSsn[$s['sid']] = ($s['ssn'] + 1) & 0xFFFF;
            while (isset($this->held[$s['sid']][$this->nextSsn[$s['sid']]])) {
                $n = $this->nextSsn[$s['sid']];
                [$p, $d] = $this->held[$s['sid']][$n];
                unset($this->held[$s['sid']][$n]);
                $this->deliver($s['sid'], $p, $d);
                $this->nextSsn[$s['sid']] = ($n + 1) & 0xFFFF;
            }
        }
        $this->advanceCum();
        return true;
    }

    private function advanceForwardTsn(): void
    {
        if (!$this->peerSupportsForwardTsn || $this->state !== self::ST_ESTABLISHED) {
            return;
        }

        $newCum = $this->peerCumTsn;

        while (true) {
            $next = ($newCum + 1) & self::MASK;
            $entry = $this->outstanding[$next] ?? null;

            if ($entry === null || (!$entry['acked'] && !$entry['abandoned'])) {
                break;
            }

            $newCum = $next;
            unset($this->outstanding[$next]);
        }

        if ($newCum === $this->peerCumTsn) {
            return;
        }

        $this->peerCumTsn = $newCum;

        $this->sendChunks([
            [192, 0, pack('N', $newCum)],
        ]);
    }

    private function hasForwardTsnParameter(string $params): bool
    {
        $offset = 0;
        $length = strlen($params);

        while ($offset + 4 <= $length) {
            $param = unpack('ntype/nlength', $params, $offset);

            if ($param === false || $param['length'] < 4) {
                return false;
            }

            $paramLength = $param['length'];

            if ($offset + $paramLength > $length) {
                return false;
            }

            if ($param['type'] === 0xC000) {
                return true;
            }

            $offset += $paramLength + ((4 - ($paramLength % 4)) % 4);
        }

        return false;
    }

    private function sendSack(): void
    {
        $gaps = [];
        if ($this->recvAbove !== []) {
            $offs = [];
            foreach (array_keys($this->recvAbove) as $t) {
                $offs[] = ($t - $this->cumRecv) & self::MASK;
            }
            sort($offs);
            $start = $prev = $offs[0];
            foreach (\array_slice($offs, 1) as $o) {
                if ($o === $prev + 1) {
                    $prev = $o;
                    continue;
                }
                $gaps[] = [$start, $prev];
                $start = $prev = $o;
            }
            $gaps[] = [$start, $prev];
            $gaps = \array_slice(array_filter($gaps, static fn (array $g): bool => $g[1] <= 0xFFFF), 0, 64);
        }
        $dups = \array_slice($this->dups, 0, 16);
        $this->dups = [];
        $v = pack('NNnn', $this->cumRecv, self::RWND, \count($gaps), \count($dups));
        foreach ($gaps as [$s, $e]) {
            $v .= pack('nn', $s, $e);
        }
        foreach ($dups as $d) {
            $v .= pack('N', $d);
        }
        $this->sendChunks([[3, 0, $v]]);
    }

    public function sendMessage(int $sid, int $ppid, string $data, bool $ordered = true): bool
    {
        return $this->queueMessage($sid, $ppid, $data, $ordered, true);
    }

    public function sendUnreliableMessage(int $sid, int $ppid, string $data): bool
    {
        if (!$this->peerSupportsForwardTsn || !$this->isEstablished()) {
            return false;
        }

        return $this->queueMessage($sid, $ppid, $data, false, false);
    }

    private function queueMessage(
        int $sid,
        int $ppid,
        string $data,
        bool $ordered,
        bool $retransmit
    ): bool {
        if ($this->state !== self::ST_ESTABLISHED) {
            return false;
        }

        $ssn = 0;

        if ($ordered) {
            $ssn = $this->outSsn[$sid] ?? 0;
            $this->outSsn[$sid] = ($ssn + 1) & 0xFFFF;
        }

        $parts = $data === '' ? [''] : str_split($data, self::MAX_PAYLOAD);
        $last = count($parts) - 1;

        foreach ($parts as $i => $part) {
            $flags = ($i === 0 ? 2 : 0)
                | ($i === $last ? 1 : 0)
                | ($ordered ? 0 : 4);

            $tsn = $this->nextTsn;
            $this->nextTsn = ($this->nextTsn + 1) & self::MASK;

            $this->pending[] = [
                'tsn' => $tsn,
                'value' => chr($flags) . pack('NnnN', $tsn, $sid, $ssn, $ppid) . $part,
                'size' => strlen($part) + 16,
                'retransmit' => $retransmit,
            ];
        }

        $this->transmitPending(microtime(true));

        return true;
    }

    /** True while queued data has not been sent, or sent data is not yet acknowledged (or abandoned). */
    public function hasUnsettledData(): bool
    {
        if ($this->pending !== []) {
            return true;
        }
        foreach ($this->outstanding as $e) {
            if (!$e['acked'] && !$e['abandoned']) {
                return true;
            }
        }
        return false;
    }

    public function flush(): void
    {
        $this->transmitPending(microtime(true));
    }

    private function transmitPending(float $now): void
    {
        $window = min($this->cwnd, $this->peerRwnd);
        while ($this->pending !== []) {
            $next = $this->pending[array_key_first($this->pending)];
            if ($this->flight > 0 && $this->flight + $next['size'] > $window) {
                break;
            }
            array_shift($this->pending);
            // value[0] = flags, the rest is the DATA chunk body
            $pkt = $this->buildPacket(self::chunk(0, \ord($next['value'][0]), substr($next['value'], 1)), $this->peerTag);
            $this->outstanding[$next['tsn']] = [
                'pkt' => $pkt,
                'size' => $next['size'],
                'sent' => $now,
                'tries' => 0,
                'acked' => false,
                'miss' => 0,
                'retransmit' => $next['retransmit'],
                'abandoned' => false,
            ];
            $this->flight += $next['size'];
            ($this->sendPacket)($pkt);
        }
    }

    private function handleSack(string $v): void
    {
        if (\strlen($v) < 12 || $this->state !== self::ST_ESTABLISHED) {
            return;
        }
        $s = unpack('Ncum/Nrwnd/ngaps/ndups', $v);
        if ($s === false) {
            return;
        }
        $now = microtime(true);
        $cum = $s['cum'];
        if ($this->tsnLe($this->peerCumTsn, $cum)) {
            $this->peerCumTsn = $cum;
        }
        $this->peerRwnd = $s['rwnd'];
        $acked = 0;
        foreach (array_keys($this->outstanding) as $tsn) {
            if (!$this->tsnLe($tsn, $cum)) {
                continue;
            }
            $e = $this->outstanding[$tsn];
            if (!$e['acked'] && !$e['abandoned']) {
                $this->flight = max(0, $this->flight - $e['size']);
                $acked += $e['size'];
                if ($e['tries'] === 0) {
                    $this->sampleRtt($now - $e['sent']);
                }
            }
            unset($this->outstanding[$tsn]);
        }
        $highest = null;
        for ($i = 0, $o = 12; $i < $s['gaps'] && $o + 4 <= \strlen($v); $i++, $o += 4) {
            $g = unpack('nstart/nend', $v, $o);
            if ($g === false) {
                break;
            }
            for ($k = $g['start']; $k <= $g['end']; $k++) {
                $tsn = ($cum + $k) & self::MASK;
                if (isset($this->outstanding[$tsn]) && !$this->outstanding[$tsn]['acked'] && !$this->outstanding[$tsn]['abandoned']) {
                    $this->outstanding[$tsn]['acked'] = true;
                    $this->flight = max(0, $this->flight - $this->outstanding[$tsn]['size']);
                    $acked += $this->outstanding[$tsn]['size'];
                }
                if ($highest === null || $this->tsnLe($highest, $tsn)) {
                    $highest = $tsn;
                }
            }
        }
        if ($highest !== null) {
            foreach (array_keys($this->outstanding) as $tsn) {
                $e = &$this->outstanding[$tsn];
                if (
                    !$e['acked']
                    && !$e['abandoned']
                    && $tsn !== $highest
                    && $this->tsnLe($tsn, $highest)
                ) {
                    $e['miss']++;
                    if ($e['miss'] === 3) {
                        if (!$e['retransmit']) {
                            $e['abandoned'] = true;
                            $this->flight = max(0, $this->flight - $e['size']);
                        } else {
                            $e['sent'] = $now;
                            $e['tries']++;
                            ($this->sendPacket)($e['pkt']);
                        }
                    }
                }
                unset($e);
            }
        }
        if ($acked > 0) {
            if ($this->cwnd < $this->ssthresh) {
                $this->cwnd = min(self::MAX_CWND, $this->cwnd + min($acked, self::MTU));
            } else {
                $this->cwnd = min(self::MAX_CWND, $this->cwnd + (int) (self::MTU * $acked / max(1, $this->cwnd)) + 1);
            }
        }
        $this->transmitPending($now);
        $this->advanceForwardTsn();
    }

    private function sampleRtt(float $r): void
    {
        if ($this->srtt < 0) {
            $this->srtt = $r;
            $this->rttvar = $r / 2;
        } else {
            $this->rttvar = 0.75 * $this->rttvar + 0.25 * abs($this->srtt - $r);
            $this->srtt = 0.875 * $this->srtt + 0.125 * $r;
        }
        $this->rto = max(0.4, min(10.0, $this->srtt + 4 * $this->rttvar));
    }

    public function tick(float $now): void
    {
        if ($this->state === self::ST_COOKIE_SENT && $this->handshakePkt !== '') {
            // T1-init / T1-cookie timer: retransmit with exponential backoff (1s, 2s, 4s, 8s), then give up.
            if ($now - $this->handshakeAt >= min(8.0, (float) (1 << $this->handshakeTries))) {
                if ($this->handshakeTries >= 4) {
                    $this->closeAssoc('SCTP handshake timeout (no INIT-ACK/COOKIE-ACK from peer)');
                    return;
                }
                $this->handshakeTries++;
                $this->handshakeAt = $now;
                Logger::debug("[NetherNet] SCTP handshake retransmit #{$this->handshakeTries}");
                ($this->sendPacket)($this->handshakePkt);
            }
            return;
        }
        if ($this->state !== self::ST_ESTABLISHED) {
            return;
        }

        $lost = false;

        foreach ($this->outstanding as $tsn => $entry) {
            if (
                $entry['acked']
                || $entry['abandoned']
                || $now - $entry['sent'] < $this->rto * (1 << min($entry['tries'], 5))
            ) {
                continue;
            }

            if (!$entry['retransmit']) {
                $this->outstanding[$tsn]['abandoned'] = true;
                $this->flight = max(0, $this->flight - $entry['size']);
                $lost = true;
                continue;
            }

            if ($entry['tries'] >= 12) {
                $this->closeAssoc('SCTP retransmission limit');
                return;
            }

            $this->outstanding[$tsn]['sent'] = $now;
            $this->outstanding[$tsn]['tries']++;

            ($this->sendPacket)($entry['pkt']);

            $lost = true;
        }

        if ($lost) {
            $this->ssthresh = max(4 * self::MTU, intdiv($this->cwnd, 2));
            $this->cwnd = self::MTU;
        }

        $this->advanceForwardTsn();
        $this->transmitPending($now);
    }

    public function close(): void
    {
        if ($this->state === self::ST_ESTABLISHED) {
            $this->sendChunks([[6, 0, '']]);
        }
        $this->state = self::ST_CLOSED;
    }

    private function closeAssoc(string $why): void
    {
        if ($this->state === self::ST_CLOSED) {
            return;
        }
        Logger::debug("[NetherNet] SCTP association closed: {$why}");
        $this->state = self::ST_CLOSED;
        ($this->onClose)($why);
    }

    /** @param array<int, array{0: int, 1: int, 2: string}> $chunks */
    private function sendChunks(array $chunks): void
    {
        $body = '';
        foreach ($chunks as [$type, $flags, $value]) {
            $body .= self::chunk($type, $flags, $value);
        }
        ($this->sendPacket)($this->buildPacket($body, $this->peerTag));
    }

    private function buildPacket(string $chunks, int $vtag): string
    {
        $p = pack('nnN', $this->localPort, $this->peerPort, $vtag) . "\0\0\0\0" . $chunks;
        return substr($p, 0, 8) . pack('V', Crc32c::compute($p)) . substr($p, 12);
    }

    private static function chunk(int $type, int $flags, string $value): string
    {
        $len = 4 + \strlen($value);
        return pack('CCn', $type, $flags, $len) . $value . str_repeat("\0", (4 - ($len % 4)) % 4);
    }

    private static function param(int $type, string $value): string
    {
        $len = 4 + \strlen($value);
        return pack('nn', $type, $len) . $value . str_repeat("\0", (4 - ($len % 4)) % 4);
    }

    /** a <= b in 32-bit serial arithmetic. */
    private function tsnLe(int $a, int $b): bool
    {
        return ((($b - $a) & self::MASK) < 0x80000000);
    }
}
