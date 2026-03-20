<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use function extension_loaded;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
abstract readonly class AESGCM implements Content_Encryption_Algorithm
{
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Please install the OpenSSL extension');
        }
    }
    #[Override]
    public function allowed_key_types(): array
    {
        return [];
        //Irrelevant
    }
    #[Override]
    public function encrypt_content(string $data, string $cek, string $iv, ?string $aad, string $encoded_protected_header, ?string &$tag = null): string
    {
        $calculated_aad = $encoded_protected_header;
        if ($aad !== null) {
            $calculated_aad .= '.' . Base64url_Safe::encode_unpadded($aad);
        }
        $tag = '';
        $result = openssl_encrypt($data, $this->get_mode(), $cek, OPENSSL_RAW_DATA, $iv, $tag, $calculated_aad);
        if ($result === false) {
            throw new RuntimeException('Unable to encrypt the content');
        }
        return $result;
    }
    #[Override]
    public function decrypt_content(string $data, string $cek, string $iv, ?string $aad, string $encoded_protected_header, string $tag): string
    {
        $calculated_aad = $encoded_protected_header;
        if ($aad !== null) {
            $calculated_aad .= '.' . Base64url_Safe::encode_unpadded($aad);
        }
        $result = openssl_decrypt($data, $this->get_mode(), $cek, OPENSSL_RAW_DATA, $iv, $tag, $calculated_aad);
        if ($result === false) {
            throw new RuntimeException('Unable to decrypt the content');
        }
        return $result;
    }
    #[Override]
    public function get_iv_size(): int
    {
        return 96;
    }
    abstract protected function get_mode(): string;
}