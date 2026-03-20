<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Key_Management\Key_Converter\Rsa_Key;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:optimize', description: 'Optimize a RSA key by calculating additional primes (CRT).')]
final class Optimize_Rsa_Key_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('jwk', Input_Argument::REQUIRED, 'The RSA key.');
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
            throw new InvalidArgumentException('Invalid JWK');
        }
        $key = Rsa_Key::create_from_jwk(new JWK($json));
        $key->optimize();
        $this->prepare_json_output($input, $output, $key->to_jwk());
        return self::SUCCESS;
    }
}