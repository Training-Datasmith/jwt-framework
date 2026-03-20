<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Content_Encryption;

use function extension_loaded;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
use function strlen;
abstract readonly class AESCBCHS implements Content_Encryption_Algorithm
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
        $k = substr($cek, $this->get_cek_size() / 16);
        $result = openssl_encrypt($data, $this->get_mode(), $k, OPENSSL_RAW_DATA, $iv);
        if ($result === false) {
            throw new RuntimeException('Unable to encrypt the content');
        }
        $tag = $this->calculate_authentication_tag($result, $cek, $iv, $aad, $encoded_protected_header);
        return $result;
    }
    #[Override]
    public function decrypt_content(string $data, string $cek, string $iv, ?string $aad, string $encoded_protected_header, string $tag): string
    {
        if (!$this->is_tag_valid($data, $cek, $iv, $aad, $encoded_protected_header, $tag)) {
            throw new RuntimeException('Unable to decrypt or to verify the tag.');
        }
        $k = substr($cek, $this->get_cek_size() / 16);
        $result = openssl_decrypt($data, $this->get_mode(), $k, OPENSSL_RAW_DATA, $iv);
        if ($result === false) {
            throw new RuntimeException('Unable to decrypt or to verify the tag.');
        }
        return $result;
    }
    #[Override]
    public function get_iv_size(): int
    {
        return 128;
    }
    protected function calculate_authentication_tag(string $encrypted_data, string $cek, string $iv, ?string $aad, string $encoded_header): string
    {
        $calculated_aad = $encoded_header;
        if ($aad !== null) {
            $calculated_aad .= '.' . Base64url_Safe::encode_unpadded($aad);
        }
        $mac_key = substr($cek, 0, $this->get_cek_size() / 16);
        $auth_data_length = strlen($encoded_header);
        $secured_input = implode('', [$calculated_aad, $iv, $encrypted_data, pack('N2', $auth_data_length / 2147483647 * 8, $auth_data_length % 2147483647 * 8)]);
        $hash = hash_hmac($this->get_hash_algorithm(), $secured_input, $mac_key, true);
        return substr($hash, 0, strlen($hash) / 2);
    }
    protected function is_tag_valid(string $encrypted_data, string $cek, string $iv, ?string $aad, string $encoded_header, string $authentication_tag): bool
    {
        return hash_equals($authentication_tag, $this->calculate_authentication_tag($encrypted_data, $cek, $iv, $aad, $encoded_header));
    }
    abstract protected function get_hash_algorithm(): string;
    abstract protected function get_mode(): string;
}