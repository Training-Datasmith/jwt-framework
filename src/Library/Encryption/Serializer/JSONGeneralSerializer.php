<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Serializer;

use function array_key_exists;
use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Recipient;
use LogicException;
use Override;
final readonly class Json_General_Serializer implements Jwe_Serializer
{
    public const NAME = 'jwe_json_general';
    #[Override]
    public function display_name(): string
    {
        return 'JWE JSON General';
    }
    #[Override]
    public function name(): string
    {
        return self::NAME;
    }
    #[Override]
    public function serialize(JWE $jwe, ?int $recipient_index = null): string
    {
        if ($jwe->count_recipients() === 0) {
            throw new LogicException('No recipient.');
        }
        $data = ['ciphertext' => Base64url_Safe::encode_unpadded($jwe->get_ciphertext() ?? ''), 'iv' => Base64url_Safe::encode_unpadded($jwe->get_iv() ?? ''), 'tag' => Base64url_Safe::encode_unpadded($jwe->get_tag() ?? '')];
        if ($jwe->get_aad() !== null) {
            $data['aad'] = Base64url_Safe::encode_unpadded($jwe->get_aad());
        }
        if (count($jwe->get_shared_protected_header()) !== 0) {
            $data['protected'] = $jwe->get_encoded_shared_protected_header();
        }
        if (count($jwe->get_shared_header()) !== 0) {
            $data['unprotected'] = $jwe->get_shared_header();
        }
        $data['recipients'] = [];
        foreach ($jwe->get_recipients() as $recipient) {
            $temp = [];
            if (count($recipient->get_header()) !== 0) {
                $temp['header'] = $recipient->get_header();
            }
            if ($recipient->get_encrypted_key() !== null) {
                $temp['encrypted_key'] = Base64url_Safe::encode_unpadded($recipient->get_encrypted_key());
            }
            $data['recipients'][] = $temp;
        }
        return Json_Converter::encode($data);
    }
    #[Override]
    public function unserialize(string $input): JWE
    {
        $data = Json_Converter::decode($input);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Unsupported input.');
        }
        $this->check_data($data);
        $ciphertext = Base64url_Safe::decode_no_padding($data['ciphertext']);
        $iv = Base64url_Safe::decode_no_padding($data['iv']);
        $tag = Base64url_Safe::decode_no_padding($data['tag']);
        $aad = array_key_exists('aad', $data) ? Base64url_Safe::decode_no_padding($data['aad']) : null;
        [$encoded_shared_protected_header, $shared_protected_header, $shared_header] = $this->process_headers($data);
        $recipients = [];
        foreach ($data['recipients'] as $recipient) {
            [$encrypted_key, $header] = $this->process_recipient($recipient);
            $recipients[] = new Recipient($header, $encrypted_key);
        }
        return new JWE($ciphertext, $iv, $tag, $aad, $shared_header, $shared_protected_header, $encoded_shared_protected_header, $recipients);
    }
    private function check_data(?array $data): void
    {
        if ($data === null || !isset($data['ciphertext']) || !isset($data['recipients'])) {
            throw new InvalidArgumentException('Unsupported input.');
        }
    }
    private function process_recipient(array $recipient): array
    {
        $encrypted_key = array_key_exists('encrypted_key', $recipient) ? Base64url_Safe::decode_no_padding($recipient['encrypted_key']) : null;
        $header = array_key_exists('header', $recipient) ? $recipient['header'] : [];
        return [$encrypted_key, $header];
    }
    private function process_headers(array $data): array
    {
        $encoded_shared_protected_header = array_key_exists('protected', $data) ? $data['protected'] : null;
        $shared_protected_header = $encoded_shared_protected_header ? Json_Converter::decode(Base64url_Safe::decode_no_padding($encoded_shared_protected_header)) : [];
        $shared_header = array_key_exists('unprotected', $data) ? $data['unprotected'] : [];
        return [$encoded_shared_protected_header, $shared_protected_header, $shared_header];
    }
}