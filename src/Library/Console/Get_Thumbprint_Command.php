<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Json_Converter;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:thumbprint', description: 'Get the thumbprint of a JWK key.')]
final class Get_Thumbprint_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('jwk', Input_Argument::REQUIRED, 'The JWK key.')->add_option('hash', null, Input_Option::VALUE_OPTIONAL, 'The hashing algorithm.', 'sha256');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $jwk = $input->get_argument('jwk');
        if (!is_string($jwk)) {
            throw new InvalidArgumentException('Invalid JWK');
        }
        $hash = $input->get_option('hash');
        if (!is_string($hash)) {
            throw new InvalidArgumentException('Invalid hash algorithm');
        }
        $json = Json_Converter::decode($jwk);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid input.');
        }
        $key = new JWK($json);
        $output->write($key->thumbprint($hash));
        return self::SUCCESS;
    }
}