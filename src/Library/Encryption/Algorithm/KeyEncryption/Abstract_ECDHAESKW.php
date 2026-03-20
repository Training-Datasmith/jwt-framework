<?php

declare (strict_types=1);
namespace Jose\Component\Encryption\Algorithm\Key_Encryption;

use AESKW\Wrapper as WrapperInterface;
use Override;
use RuntimeException;
abstract readonly class Abstract_Ecdhaeskw implements Key_Agreement_With_Key_Wrapping
{
    public function __construct()
    {
        if (!interface_exists(Wrapper_Interface::class)) {
            throw new RuntimeException('Please install "spomky-labs/aes-key-wrap" to use AES-KW algorithms');
        }
    }
    #[Override]
    public function allowed_key_types(): array
    {
        return ['EC', 'OKP'];
    }
    #[Override]
    public function get_key_management_mode(): string
    {
        return self::MODE_WRAP;
    }
    abstract protected function get_wrapper(): Wrapper_Interface;
    abstract protected function get_key_length(): int;
}