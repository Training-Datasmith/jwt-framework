<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Serializer;

use function count;
use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\Recipient;
use LogicException;
use Override;
use function sprintf;
use Throwable;
final readonly class Compact_Serializer implements Jwe_Serializer
{
    public const NAME = 'jwe_compact';
    #[Override]
    public function display_name(): string
    {
        return 'JWE Compact';
    }
    #[Override]
    public function name(): string
    {
        return self::NAME;
    }
    #[Override]
    public function serialize(JWE $jwe, ?int $recipient_index = null): string
    {
        if ($recipient_index === null) {
            $recipient_index = 0;
        }
        $recipient = $jwe->get_recipient($recipient_index);
        $this->check_has_no_aad($jwe);
        $this->check_has_shared_protected_header($jwe);
        $this->check_recipient_has_no_header($jwe, $recipient_index);
        return sprintf('%s.%s.%s.%s.%s', $jwe->get_encoded_shared_protected_header(), Base64url_Safe::encode_unpadded($recipient->get_encrypted_key() ?? ''), Base64url_Safe::encode_unpadded($jwe->get_iv() ?? ''), Base64url_Safe::encode_unpadded($jwe->get_ciphertext() ?? ''), Base64url_Safe::encode_unpadded($jwe->get_tag() ?? ''));
    }
    #[Override]
    public function unserialize(string $input): JWE
    {
        $parts = explode('.', $input);
        if (count($parts) !== 5) {
            throw new InvalidArgumentException('Unsupported input');
        }
        try {
            $encoded_shared_protected_header = $parts[0];
            $shared_protected_header = Json_Converter::decode(Base64url_Safe::decode_no_padding($encoded_shared_protected_header));
            if (!is_array($shared_protected_header)) {
                throw new InvalidArgumentException('Unsupported input.');
            }
            $encrypted_key = $parts[1] === '' ? null : Base64url_Safe::decode_no_padding($parts[1]);
            $iv = Base64url_Safe::decode_no_padding($parts[2]);
            $ciphertext = Base64url_Safe::decode_no_padding($parts[3]);
            $tag = Base64url_Safe::decode_no_padding($parts[4]);
            return new JWE($ciphertext, $iv, $tag, null, [], $shared_protected_header, $encoded_shared_protected_header, [new Recipient([], $encrypted_key)]);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException('Unsupported input', $throwable->get_code(), $throwable);
        }
    }
    private function check_has_no_aad(JWE $jwe): void
    {
        if ($jwe->get_aad() !== null) {
            throw new LogicException('This JWE has AAD and cannot be converted into Compact JSON.');
        }
    }
    private function check_recipient_has_no_header(JWE $jwe, int $id): void
    {
        if (count($jwe->get_shared_header()) !== 0 || count($jwe->get_recipient($id)->get_header()) !== 0) {
            throw new LogicException('This JWE has shared header parameters or recipient header parameters and cannot be converted into Compact JSON.');
        }
    }
    private function check_has_shared_protected_header(JWE $jwe): void
    {
        if (count($jwe->get_shared_protected_header()) === 0) {
            throw new LogicException('This JWE does not have shared protected header parameters and cannot be converted into Compact JSON.');
        }
    }
}