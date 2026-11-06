<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

use Closure;
use OpenSSLAsymmetricKey;
use watermossmc\util\Logger;

final class DtlsServer
{
    private const ST_WAIT_HELLO = 0;
    private const ST_FLIGHT_SENT = 1;
    private const ST_ESTABLISHED = 2;
    private const ST_CLOSED = 3;

    private const CT_CCS = 20;
    private const CT_ALERT = 21;
    private const CT_HANDSHAKE = 22;
    private const CT_APPDATA = 23;

    private const HS_CLIENT_HELLO = 1;
    private const HS_SERVER_HELLO = 2;
    private const HS_CERTIFICATE = 11;
    private const HS_SERVER_KEY_EXCHANGE = 12;
    private const HS_CERTIFICATE_REQUEST = 13;
    private const HS_SERVER_HELLO_DONE = 14;
    private const HS_CERTIFICATE_VERIFY = 15;
    private const HS_CLIENT_KEY_EXCHANGE = 16;
    private const HS_FINISHED = 20;

    private const MAX_DATAGRAM = 1200;
    private const MAX_FRAGMENT = 1000;

    private int $state = self::ST_WAIT_HELLO;
    private string $transcript = '';
    private int $sendMsgSeq = 0;
    private int $nextRecvSeq = 0;

    /** @var array<int, array{type: int, len: int, buf: string, have: array<int, int>}> */
    private array $partial = [];

    /** @var array<int, array{0: int, 1: string}> */
    private array $ready = [];

    private string $clientRandom = '';
    private string $serverRandom = '';
    private bool $ems = false;
    private bool $reneg = false;
    private string $srtpProfile = '';
    private ?OpenSSLAsymmetricKey $ecdhe = null;
    private string $master = '';
    private string $clientKey = '';
    private string $serverKey = '';
    private string $clientIv = '';
    private string $serverIv = '';
    private bool $keysReady = false;
    private int $readEpoch = 0;
    private int $writeEpoch = 0;
    private int $curEpoch = 0;
    private int $wseq0 = 0;
    private int $wseq1 = 0;
    private ?string $clientCertDer = null;
    private bool $clientCertVerified = false;

    /** @var string[] */
    private array $lastFlight = [];
    private bool $finalFlightPending = false;
    private float $flightAt = 0.0;
    private int $retries = 0;

    /**
     * @param Closure(string): void $send          sends a UDP datagram to the peer
     * @param Closure(string): void $onData        decrypted application data (SCTP)
     * @param Closure(string): void $onClose       connection closed or failed
     * @param Closure(): void       $onEstablished handshake finished
     */
    public function __construct(
        private readonly DtlsIdentity $identity,
        private readonly string $expectedFingerprint,
        private readonly Closure $send,
        private readonly Closure $onData,
        private readonly Closure $onClose,
        private readonly Closure $onEstablished
    ) {
    }

    public function isEstablished(): bool
    {
        return $this->state === self::ST_ESTABLISHED;
    }

    public function isClosed(): bool
    {
        return $this->state === self::ST_CLOSED;
    }

    public function receive(string $datagram): void
    {
        Logger::debug(sprintf('[NetherNet] DTLS datagram received | size=%d | state=%d', strlen($datagram), $this->state));

        $off = 0;
        $n = \strlen($datagram);
        while ($this->state !== self::ST_CLOSED && $off + 13 <= $n) {
            $type = \ord($datagram[$off]);
            $epochSeq = substr($datagram, $off + 3, 8);
            $epoch = (\ord($epochSeq[0]) << 8) | \ord($epochSeq[1]);
            $len = (\ord($datagram[$off + 11]) << 8) | \ord($datagram[$off + 12]);
            if ($off + 13 + $len > $n) {
                break;
            }
            $body = substr($datagram, $off + 13, $len);
            $off += 13 + $len;
            $this->handleRecord($type, $epoch, $epochSeq, $body);
        }
    }

    public function send(string $data): bool
    {
        if ($this->state !== self::ST_ESTABLISHED) {
            return false;
        }
        ($this->send)($this->record(self::CT_APPDATA, $data));
        return true;
    }

    public function tick(float $now): void
    {
        if (($this->state !== self::ST_FLIGHT_SENT && !$this->finalFlightPending) || $this->lastFlight === []) {
            return;
        }
        if ($now - $this->flightAt < (1 << min($this->retries, 4))) {
            return;
        }
        if ($this->retries >= 6) {
            $this->fail('DTLS handshake timeout', 90);
            return;
        }
        $this->retries++;
        $this->flightAt = $now;
        foreach ($this->lastFlight as $d) {
            ($this->send)($d);
        }
    }

    public function close(): void
    {
        if ($this->state === self::ST_CLOSED) {
            return;
        }
        if ($this->state === self::ST_ESTABLISHED) {
            ($this->send)($this->record(self::CT_ALERT, "\x01\x00"));
        }
        $this->state = self::ST_CLOSED;
    }

    private function handleRecord(int $type, int $epoch, string $epochSeq, string $body): void
    {
        if ($epoch === 0) {
            $plain = $body;
        } else {
            if ($epoch !== 1 || !$this->keysReady || $this->readEpoch < 1) {
                return;
            }
            $plain = $this->decrypt($type, $epochSeq, $body);
            if ($plain === null) {
              Logger::debug(sprintf('[NetherNet] DTLS decrypt failed | type=0x%02X | epoch=%d | size=%d', $type, $epoch, strlen($body)));
                return;
            }
        }
        $this->curEpoch = $epoch;
        Logger::debug(sprintf(
            '[NetherNet] DTLS record decrypted | type=0x%02X | epoch=%d | state=%d | size=%d',
            $type,
            $epoch,
            $this->state,
            strlen($plain)
        ));
        switch ($type) {
            case self::CT_HANDSHAKE:
                $this->handleHandshakeRecord($plain);
                break;
            case self::CT_CCS:
                if ($epoch === 0 && $this->keysReady && $plain === "\x01") {
                    $this->readEpoch = 1;
                    Logger::debug('[NetherNet] DTLS client CCS accepted | readEpoch=1');
                }
                break;
            case self::CT_ALERT:
                if (\strlen($plain) >= 2) {
                    $level = \ord($plain[0]);
                    $desc = \ord($plain[1]);
                    if ($desc === 0 || $level === 2) {
                        $this->state = self::ST_CLOSED;
                        ($this->onClose)($desc === 0 ? 'DTLS close_notify' : "DTLS alert {$desc}");
                    }
                }
                break;
            case self::CT_APPDATA:
                Logger::debug(sprintf('[NetherNet] DTLS APPDATA | state=%d | epoch=%d | size=%d', $this->state, $epoch, strlen($plain)));
                if ($this->state === self::ST_ESTABLISHED && $epoch === 1) {
                    $this->finalFlightPending = false;
                    $this->lastFlight = [];
                    ($this->onData)($plain);
                }
                break;
        }
    }

    private function record(int $type, string $payload): string
    {
        $len = \strlen($payload);
        if ($this->writeEpoch === 0) {
            $seq = $this->wseq0++;
            return \chr($type) . "\xFE\xFD" . "\x00\x00" . substr(pack('J', $seq), 2) . pack('n', $len) . $payload;
        }
        $epochSeq = "\x00\x01" . substr(pack('J', $this->wseq1++), 2);
        $nonce = $this->serverIv . $epochSeq;
        $aad = $epochSeq . \chr($type) . "\xFE\xFD" . pack('n', $len);
        $tag = '';
        $ct = openssl_encrypt($payload, 'aes-128-gcm', $this->serverKey, \OPENSSL_RAW_DATA, $nonce, $tag, $aad, 16);
        if ($ct === false) {
            $ct = '';
            $tag = str_repeat("\0", 16);
        }
        $body = $epochSeq . $ct . $tag;
        Logger::debug(sprintf('[NetherNet] DTLS TX encrypted | type=0x%02X | epoch=1 | seq=%d | body=%d | nonce=%s', $type, $this->wseq1 - 1, strlen($body), bin2hex($nonce)));
        return \chr($type) . "\xFE\xFD" . $epochSeq . pack('n', \strlen($body)) . $body;
    }

    private function decrypt(int $type, string $epochSeq, string $body): ?string
    {
        if (\strlen($body) < 24) {
            return null;
        }
        $nonce = $this->clientIv . substr($body, 0, 8);
        $tag = substr($body, -16);
        $ct = substr($body, 8, \strlen($body) - 24);
        $aad = $epochSeq . \chr($type) . "\xFE\xFD" . pack('n', \strlen($ct));
        $pt = openssl_decrypt($ct, 'aes-128-gcm', $this->clientKey, \OPENSSL_RAW_DATA, $nonce, $tag, $aad);
        return $pt === false ? null : $pt;
    }

    private function handleHandshakeRecord(string $d): void
    {
        $off = 0;
        $n = \strlen($d);
        while ($off + 12 <= $n && $this->state !== self::ST_CLOSED) {
            $type = \ord($d[$off]);
            $len = self::readU24($d, $off + 1);
            $mseq = (\ord($d[$off + 4]) << 8) | \ord($d[$off + 5]);
            $foff = self::readU24($d, $off + 6);
            $flen = self::readU24($d, $off + 9);
            if ($off + 12 + $flen > $n || $foff + $flen > $len || $len > 65535) {
                return;
            }
            $frag = substr($d, $off + 12, $flen);
            $off += 12 + $flen;
            $this->acceptFragment($type, $len, $mseq, $foff, $frag);
        }
    }

    private function acceptFragment(int $type, int $len, int $mseq, int $foff, string $frag): void
    {
        if ($mseq < $this->nextRecvSeq) {
            $this->resendFlight();
            return;
        }
        if ($foff === 0 && \strlen($frag) === $len) {
            $this->ready[$mseq] = [$type, $frag];
        } else {
            $p = $this->partial[$mseq] ?? ['type' => $type, 'len' => $len, 'buf' => str_repeat("\0", $len), 'have' => []];
            $p['buf'] = substr_replace($p['buf'], $frag, $foff, \strlen($frag));
            $p['have'][$foff] = \strlen($frag);
            ksort($p['have']);
            $pos = 0;
            foreach ($p['have'] as $o => $l) {
                if ($o > $pos) {
                    break;
                }
                $pos = max($pos, $o + $l);
            }
            if ($pos >= $len) {
                $this->ready[$mseq] = [$type, $p['buf']];
                unset($this->partial[$mseq]);
            } else {
                $this->partial[$mseq] = $p;
            }
        }
        while ($this->state !== self::ST_CLOSED && isset($this->ready[$this->nextRecvSeq])) {
            [$t, $body] = $this->ready[$this->nextRecvSeq];
            $seq = $this->nextRecvSeq;
            unset($this->ready[$seq]);
            $this->nextRecvSeq++;
            $this->processMessage($t, $seq, $body);
        }
    }

    private function processMessage(int $type, int $seq, string $body): void
    {
        $canon = \chr($type) . self::u24(\strlen($body)) . pack('n', $seq) . self::u24(0) . self::u24(\strlen($body)) . $body;
        switch ($type) {
            case self::HS_CLIENT_HELLO:
                if ($this->state !== self::ST_WAIT_HELLO) {
                    return;
                }
                $this->transcript .= $canon;
                $this->onClientHello($body);
                return;
            case self::HS_CERTIFICATE:
                $this->onClientCertificate($body);
                break;
            case self::HS_CLIENT_KEY_EXCHANGE:
                $this->transcript .= $canon;
                $this->onClientKeyExchange($body);
                return;
            case self::HS_CERTIFICATE_VERIFY:
                $this->onCertificateVerify($body);
                break;
            case self::HS_FINISHED:
                $this->onClientFinished($body, $canon);
                return;
            default:
                $this->fail('Unexpected handshake message ' . $type, 10);
                return;
        }
        $this->transcript .= $canon;
    }

    private function onClientHello(string $b): void
    {
        $n = \strlen($b);
        if ($n < 39) {
            $this->fail('ClientHello too short', 50);
            return;
        }
        $o = 2;
        $this->clientRandom = substr($b, $o, 32);
        $o += 32;
        $sidLen = \ord($b[$o]);
        $o += 1 + $sidLen;
        if ($o >= $n) {
            $this->fail('Bad ClientHello', 50);
            return;
        }
        $cookieLen = \ord($b[$o]);
        $o += 1 + $cookieLen;
        if ($o + 2 > $n) {
            $this->fail('Bad ClientHello', 50);
            return;
        }
        $csLen = (\ord($b[$o]) << 8) | \ord($b[$o + 1]);
        $o += 2;
        if ($o + $csLen >= $n) {
            $this->fail('Bad ClientHello', 50);
            return;
        }
        $suites = str_split(substr($b, $o, $csLen), 2);
        $o += $csLen;
        $o += 1 + \ord($b[$o]);
        $exts = [];
        if ($o + 2 <= $n) {
            $extLen = (\ord($b[$o]) << 8) | \ord($b[$o + 1]);
            $o += 2;
            $end = min($n, $o + $extLen);
            while ($o + 4 <= $end) {
                $et = (\ord($b[$o]) << 8) | \ord($b[$o + 1]);
                $el = (\ord($b[$o + 2]) << 8) | \ord($b[$o + 3]);
                $exts[$et] = substr($b, $o + 4, $el);
                $o += 4 + $el;
            }
        }
        if (!\in_array("\xC0\x2B", $suites, true)) {
            $this->fail('No common cipher suite', 40);
            return;
        }
        $this->ems = isset($exts[0x0017]);
        $this->reneg = isset($exts[0xff01]);
        $this->srtpProfile = self::pickSrtpProfile($exts[0x000e] ?? null);
        Logger::debug(sprintf('[NetherNet] ClientHello accepted | EMS=%s | Renegotiation=%s', $this->ems ? 'yes' : 'no', $this->reneg ? 'yes' : 'no'));
        $this->sendServerFlight();
    }

    private function sendServerFlight(): void
    {
        $this->serverRandom = pack('N', time()) . random_bytes(28);

        $extensions = '';
        if ($this->ems) {
            $extensions .= "\x00\x17\x00\x00";
        }
        if ($this->reneg) {
            $extensions .= "\xff\x01\x00\x01\x00";
        }
        $extensions .= "\x00\x0b\x00\x02\x01\x00";
        if ($this->srtpProfile !== '') {
            $extensions .= "\x00\x0e\x00\x05\x00\x02" . $this->srtpProfile . "\x00";
            Logger::debug('[NetherNet] use_srtp negotiated | profile=0x' . bin2hex($this->srtpProfile));
        }
        $serverHello = "\xFE\xFD" . $this->serverRandom . "\x00" . "\xC0\x2B" . "\x00" . pack('n', \strlen($extensions)) . $extensions;

        $key = openssl_pkey_new($this->identity->opensslOptions);
        $details = $key === false ? false : openssl_pkey_get_details($key);
        if ($key === false || $details === false || !isset($details['ec']['x'], $details['ec']['y'])) {
            $this->fail('ECDHE key generation failed', 80);
            return;
        }
        $this->ecdhe = $key;
        $point = "\x04" . str_pad($details['ec']['x'], 32, "\0", \STR_PAD_LEFT) . str_pad($details['ec']['y'], 32, "\0", \STR_PAD_LEFT);
        $params = "\x03\x00\x17" . \chr(65) . $point;
        $sig = '';
        if (!openssl_sign($this->clientRandom . $this->serverRandom . $params, $sig, $this->identity->privateKey, \OPENSSL_ALGO_SHA256)) {
            $this->fail('ServerKeyExchange signing failed', 80);
            return;
        }
        $serverKeyExchange = $params . "\x04\x03" . pack('n', \strlen($sig)) . $sig;
        $der = $this->identity->certDer;
        $certificate = self::u24(3 + \strlen($der)) . self::u24(\strlen($der)) . $der;
        $certificateRequest = "\x01\x40" . "\x00\x02\x04\x03" . "\x00\x00";

        $records = [];
        foreach ([
            [self::HS_SERVER_HELLO, $serverHello],
            [self::HS_CERTIFICATE, $certificate],
            [self::HS_SERVER_KEY_EXCHANGE, $serverKeyExchange],
            [self::HS_CERTIFICATE_REQUEST, $certificateRequest],
            [self::HS_SERVER_HELLO_DONE, ''],
        ] as [$t, $body]) {
            foreach ($this->handshakeRecords($t, $body) as $r) {
                $records[] = $r;
            }
        }
        $this->state = self::ST_FLIGHT_SENT;
        $this->sendFlight($records);
    }

    private function onClientCertificate(string $b): void
    {
        if (\strlen($b) < 6 || self::readU24($b, 0) === 0) {
            $this->fail('Client certificate required', 42);
            return;
        }
        $certLen = self::readU24($b, 3);
        $der = substr($b, 6, $certLen);
        if (\strlen($der) !== $certLen) {
            $this->fail('Bad client certificate', 42);
            return;
        }
        if ($this->expectedFingerprint !== '' && !hash_equals($this->expectedFingerprint, hash('sha256', $der))) {
            $this->fail('Client certificate fingerprint mismatch', 42);
            return;
        }
        $this->clientCertDer = $der;
    }

    private function onClientKeyExchange(string $b): void
    {
        if ($this->ecdhe === null || \strlen($b) < 66 || \ord($b[0]) !== 65 || $b[1] !== "\x04") {
            $this->fail('Bad ClientKeyExchange', 40);
            return;
        }
        $spki = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode((string) $spki . substr($b, 1, 65)), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $peer = openssl_pkey_get_public($pem);
        $pms = $peer === false ? false : openssl_pkey_derive($peer, $this->ecdhe);
        if ($pms === false) {
            $this->fail('ECDH derive failed', 40);
            return;
        }
        $pms = str_pad($pms, 32, "\0", \STR_PAD_LEFT);
        $this->master = $this->ems
            ? self::prf($pms, 'extended master secret', hash('sha256', $this->transcript, true), 48)
            : self::prf($pms, 'master secret', $this->clientRandom . $this->serverRandom, 48);
        $kb = self::prf($this->master, 'key expansion', $this->serverRandom . $this->clientRandom, 40);
        $this->clientKey = substr($kb, 0, 16);
        $this->serverKey = substr($kb, 16, 16);
        $this->clientIv = substr($kb, 32, 4);
        $this->serverIv = substr($kb, 36, 4);
        $this->keysReady = true;
        $this->ecdhe = null;
        Logger::debug('[NetherNet] DTLS session keys derived');
    }

    private function onCertificateVerify(string $b): void
    {
        if ($this->clientCertDer === null || \strlen($b) < 4) {
            $this->fail('Unexpected CertificateVerify', 10);
            return;
        }
        $alg = substr($b, 0, 2);
        $sigLen = (\ord($b[2]) << 8) | \ord($b[3]);
        $sig = substr($b, 4, $sigLen);
        if (($alg !== "\x04\x03" && $alg !== "\x04\x01") || \strlen($sig) !== $sigLen) {
            $this->fail('Unsupported CertificateVerify algorithm', 40);
            return;
        }
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($this->clientCertDer), 64, "\n") . "-----END CERTIFICATE-----\n";
        $pub = openssl_pkey_get_public($pem);
        if ($pub === false || openssl_verify($this->transcript, $sig, $pub, \OPENSSL_ALGO_SHA256) !== 1) {
            $this->fail('CertificateVerify failed', 51);
            return;
        }
        $this->clientCertVerified = true;
    }

    private function onClientFinished(string $body, string $canon): void
    {
        if ($this->curEpoch !== 1 || !$this->keysReady || !$this->clientCertVerified) {
            $this->fail('Unexpected Finished', 10);
            return;
        }
        $expect = self::prf($this->master, 'client finished', hash('sha256', $this->transcript, true), 12);
        if (!hash_equals($expect, $body)) {
            $this->fail('Finished verification failed', 51);
            return;
        }
        Logger::debug('[NetherNet] Client Finished verified');
        $this->transcript .= $canon;
        $ccs = $this->record(self::CT_CCS, "\x01");
        $this->writeEpoch = 1;
        $verify = self::prf($this->master, 'server finished', hash('sha256', $this->transcript, true), 12);
        $records = array_merge([$ccs], $this->handshakeRecords(self::HS_FINISHED, $verify));
        $this->sendFlight($records);
        $this->state = self::ST_ESTABLISHED;
        $this->finalFlightPending = true;
        ($this->onEstablished)();
        Logger::debug('[NetherNet] DTLS handshake established');
    }

    /** @return string[] ready-to-send records (one per fragment) */
    private function handshakeRecords(int $type, string $body): array
    {
        $seq = $this->sendMsgSeq++;
        $len = \strlen($body);
        $this->transcript .= \chr($type) . self::u24($len) . pack('n', $seq) . self::u24(0) . self::u24($len) . $body;
        $out = [];
        $off = 0;
        do {
            $chunk = substr($body, $off, self::MAX_FRAGMENT);
            $frag = \chr($type) . self::u24($len) . pack('n', $seq) . self::u24($off) . self::u24(\strlen($chunk)) . $chunk;
            $out[] = $this->record(self::CT_HANDSHAKE, $frag);
            $off += self::MAX_FRAGMENT;
        } while ($off < $len);
        return $out;
    }

    /** @param string[] $records */
    private function sendFlight(array $records): void
    {
        $this->lastFlight = [];
        $cur = '';
        foreach ($records as $r) {
            if ($cur !== '' && \strlen($cur) + \strlen($r) > self::MAX_DATAGRAM) {
                $this->lastFlight[] = $cur;
                $cur = '';
            }
            $cur .= $r;
        }
        if ($cur !== '') {
            $this->lastFlight[] = $cur;
        }
        $this->flightAt = microtime(true);
        $this->retries = 0;
        Logger::debug(sprintf('[NetherNet] DTLS sending flight | records=%d | datagrams=%d | state=%d', count($records), count($this->lastFlight), $this->state));
        foreach ($this->lastFlight as $d) {
            ($this->send)($d);
        }
    }

    private function resendFlight(): void
    {
        $now = microtime(true);
        if ($this->lastFlight === [] || $now - $this->flightAt < 0.2) {
            return;
        }
        $this->flightAt = $now;
        foreach ($this->lastFlight as $d) {
            ($this->send)($d);
        }
    }

    private function fail(string $reason, int $alertDesc): void
    {
        if ($this->state === self::ST_CLOSED) {
            return;
        }
        Logger::debug("[NetherNet] DTLS handshake failed: {$reason}");
        ($this->send)($this->record(self::CT_ALERT, "\x02" . \chr($alertDesc)));
        $this->state = self::ST_CLOSED;
        ($this->onClose)($reason);
    }

    private static function pickSrtpProfile(?string $ext): string
    {
        if ($ext === null || \strlen($ext) < 4) {
            return '';
        }
        $len = (\ord($ext[0]) << 8) | \ord($ext[1]);
        $list = str_split(substr($ext, 2, min($len, \strlen($ext) - 2) & ~1), 2);
        foreach (["\x00\x01", "\x00\x07"] as $preferred) {
            if (\in_array($preferred, $list, true)) {
                return $preferred;
            }
        }
        return $list[0] ?? '';
    }

    private static function prf(string $secret, string $label, string $seed, int $length): string
    {
        $seed = $label . $seed;
        $a = $seed;
        $out = '';
        while (\strlen($out) < $length) {
            $a = hash_hmac('sha256', $a, $secret, true);
            $out .= hash_hmac('sha256', $a . $seed, $secret, true);
        }
        return substr($out, 0, $length);
    }

    private static function u24(int $v): string
    {
        return substr(pack('N', $v), 1);
    }

    private static function readU24(string $d, int $o): int
    {
        return (\ord($d[$o]) << 16) | (\ord($d[$o + 1]) << 8) | \ord($d[$o + 2]);
    }
}
