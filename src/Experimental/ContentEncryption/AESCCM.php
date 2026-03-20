<?php

declare (strict_types=1);
namespace Jose\Experimental\Content_Encryption;

use Jose\Component\Encryption\Algorithm\Content_Encryption_Algorithm;
use const OPENSSL_RAW_DATA;
use Override;
use RuntimeException;
abstract readonly class AESCCM implements Content_Encryption_Algorithm
{
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
            $calculated_aad .= '.' . $aad;
        }
        $tag = '';
        $result = openssl_encrypt($data, $this->get_mode(), $cek, OPENSSL_RAW_DATA, $iv, $tag, $calculated_aad, $this->get_tag_length());
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
            $calculated_aad .= '.' . $aad;
        }
        $result = openssl_decrypt($data, $this->get_mode(), $cek, OPENSSL_RAW_DATA, $iv, $tag, $calculated_aad);
        if ($result === false) {
            throw new RuntimeException('Unable to decrypt the content');
        }
        return $result;
    }
    abstract protected function get_mode(): string;
    abstract protected function get_tag_length(): int;
}