<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Ec_Key;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Core\Util\Rsa_Key;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:convert:pkcs1', description: 'Converts a RSA or EC key into PKCS#1 key.')]
final class Pem_Converter_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('jwk', Input_Argument::REQUIRED, 'The key');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $jwk = $input->get_argument('jwk');
        if (!is_string($jwk)) {
            throw new InvalidArgumentException('Invalid JWK');
        }
        $json = Json_Converter::decode($jwk);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid JWK.');
        }
        $key = new JWK($json);
        $pem = match ($key->get('kty')) {
            'RSA' => Rsa_Key::create_from_jwk($key)->to_pem(),
            'EC' => Ec_Key::convert_to_pem($key),
            default => throw new InvalidArgumentException('Not a RSA or EC key.'),
        };
        $output->write($pem);
        return self::SUCCESS;
    }
}