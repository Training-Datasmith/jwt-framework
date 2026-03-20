<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use function count;
use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:rotate', description: 'Rotate a key set.')]
final class Rotate_Keyset_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command removes the last key in a key set a place a new one at the beginning.')->add_argument('jwkset', Input_Argument::REQUIRED, 'The JWKSet object')->add_argument('jwk', Input_Argument::REQUIRED, 'The new JWK object');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $jwkset = $this->get_keyset($input)->all();
        $jwk = $this->get_key($input);
        if (count($jwkset) !== 0) {
            array_pop($jwkset);
        }
        array_unshift($jwkset, $jwk);
        $this->prepare_json_output($input, $output, new Jwk_Set($jwkset));
        return self::SUCCESS;
    }
    private function get_keyset(Input_Interface $input): Jwk_Set
    {
        $jwkset = $input->get_argument('jwkset');
        if (!is_string($jwkset)) {
            throw new InvalidArgumentException('Invalid JWKSet');
        }
        $json = Json_Converter::decode($jwkset);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid JWKSet');
        }
        return Jwk_Set::create_from_key_data($json);
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