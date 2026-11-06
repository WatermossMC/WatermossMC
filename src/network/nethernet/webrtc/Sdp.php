<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

final class Sdp
{
    /**
     * @return array{ufrag: string, pwd: string, fingerprint: string, mid: string, sctpPort: int}|null
     *         The fingerprint is lowercase SHA-256 hex without colons.
     */
    public static function parseOffer(string $sdp): ?array
    {
        $sections = ['session' => [], 'app' => [], 'other' => []];
        $cur = 'session';
        foreach (preg_split('/\r\n|\n|\r/', $sdp) ?: [] as $line) {
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, 'm=')) {
                $cur = str_starts_with($line, 'm=application') ? 'app' : 'other';
                continue;
            }
            if (!str_starts_with($line, 'a=')) {
                continue;
            }
            $attr = substr($line, 2);
            $p = strpos($attr, ':');
            $name = $p === false ? $attr : substr($attr, 0, $p);
            $value = $p === false ? '' : substr($attr, $p + 1);
            $sections[$cur][$name] = $value;
        }
        $a = array_merge($sections['session'], $sections['app']);
        if (!isset($a['ice-ufrag'], $a['ice-pwd'], $a['fingerprint'])) {
            return null;
        }
        $fp = explode(' ', $a['fingerprint'], 2);
        if (\count($fp) !== 2 || strtolower($fp[0]) !== 'sha-256') {
            return null;
        }
        return [
            'ufrag' => $a['ice-ufrag'],
            'pwd' => $a['ice-pwd'],
            'fingerprint' => strtolower(str_replace(':', '', trim($fp[1]))),
            'mid' => $a['mid'] ?? '0',
            'sctpPort' => isset($a['sctp-port']) ? (int) $a['sctp-port'] : 5000,
        ];
    }

    /**
     * Builds an ICE-lite answer with the DTLS role set to passive (server).
     * A null $ip means trickle: candidates are sent separately via CANDIDATEADD.
     */
    public static function buildAnswer(
        string $mid,
        string $ufrag,
        string $pwd,
        string $fingerprint,
        string $identity,
        ?string $ip = null,
        int $port = 0,
        int $sctpPort = 5000
    ): string {
        $candidate = $ip === null ? [] : [
            'a=candidate:1 1 udp 2130706431 ' . $ip . ' ' . $port . ' typ host',
            'a=end-of-candidates',
        ];
        $lines = [
            'v=0',
            'o=- ' . random_int(1_000_000_000, 9_999_999_999) . ' 2 IN IP4 127.0.0.1',
            's=-',
            't=0 0',
            'a=group:BUNDLE ' . $mid,
            'a=extmap-allow-mixed',
            'a=msid-semantic: WMS',
            'a=identity:' . $identity,
            'm=application 9 UDP/DTLS/SCTP webrtc-datachannel',
            'c=IN IP4 0.0.0.0',
            'a=ice-lite',
            'a=ice-ufrag:' . $ufrag,
            'a=ice-pwd:' . $pwd,
            'a=fingerprint:sha-256 ' . $fingerprint,
            // We are the DTLS server; an answer must not say actpass (RFC 5763 5 / JSEP: answerer picks active or passive).
            'a=setup:passive',
            'a=mid:' . $mid,
            'a=sctp-port:' . $sctpPort,
            'a=max-message-size:262144',
            ...$candidate,
        ];
        return implode("\r\n", $lines) . "\r\n";
    }
}
