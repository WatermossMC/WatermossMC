<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet\webrtc;

use OpenSSLAsymmetricKey;
use RuntimeException;

final class DtlsIdentity
{
    /**
     * @param array<string, mixed> $opensslOptions
     */
    private function __construct(
        public readonly OpenSSLAsymmetricKey $privateKey,
        public readonly string $certDer,
        public readonly string $fingerprint,
        public readonly array $opensslOptions
    ) {
    }

    public static function generate(): self
    {
        $cfg = tempnam(sys_get_temp_dir(), 'wmcssl');
        if ($cfg === false) {
            throw new RuntimeException('Cannot create temporary OpenSSL config');
        }
        file_put_contents($cfg, "[req]\ndistinguished_name=dn\nprompt=no\n[dn]\nCN=WatermossMC\n");
        register_shutdown_function(static function () use ($cfg): void {
            @unlink($cfg);
        });

        $opts = [
            'config' => $cfg,
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'digest_alg' => 'sha256',
        ];
        $key = openssl_pkey_new($opts);
        if ($key === false) {
            throw new RuntimeException('openssl_pkey_new failed: ' . (string) openssl_error_string());
        }
        $csr = openssl_csr_new(['commonName' => 'WatermossMC'], $key, $opts);
        if ($csr === false) {
            throw new RuntimeException('openssl_csr_new failed: ' . (string) openssl_error_string());
        }
        $cert = openssl_csr_sign($csr, null, $key, 3650, $opts, random_int(1, 0x7FFFFFFF));
        if ($cert === false || !openssl_x509_export($cert, $pem)) {
            throw new RuntimeException('Certificate generation failed: ' . (string) openssl_error_string());
        }
        if (preg_match('/-----BEGIN CERTIFICATE-----(.+?)-----END CERTIFICATE-----/s', $pem, $m) !== 1) {
            throw new RuntimeException('Cannot parse generated certificate');
        }
        $der = base64_decode((string) preg_replace('/\s+/', '', $m[1]), true);
        if ($der === false) {
            throw new RuntimeException('Invalid certificate encoding');
        }
        $fingerprint = strtoupper(implode(':', str_split(hash('sha256', $der), 2)));
        return new self($key, $der, $fingerprint, $opts);
    }
}
