<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:generate:none', description: 'Generate a none key (JWK format). This key type is only supposed to be used with the "none" algorithm.')]
final class None_Key_Generator_Command extends Generator_Command
{
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $args = $this->get_options($input);
        $jwk = Jwk_Factory::create_none_key($args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}