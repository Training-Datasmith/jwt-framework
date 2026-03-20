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
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:convert:public', description: 'Convert a private key into public key. Symmetric keys (shared keys) are not changed.')]
final class Public_Key_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command converts a private key into a public key.')->add_argument('jwk', Input_Argument::REQUIRED, 'The JWK object');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $jwk = $this->get_key($input);
        $jwk = $jwk->to_public();
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
    private function get_key(Input_Interface $input): JWK
    {
        $jwk = $input->get_argument('jwk');
        if (!is_string($jwk)) {
            throw new InvalidArgumentException('Invalid JWK');
        }
        $json = Json_Converter::decode($jwk);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid JWK');
        }
        return new JWK($json);
    }
}