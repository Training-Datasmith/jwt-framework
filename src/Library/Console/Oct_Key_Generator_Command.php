<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:generate:oct', description: 'Generate an octet key (JWK format)')]
final class Oct_Key_Generator_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('size', Input_Argument::REQUIRED, 'Key size.');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $size = (int) $input->get_argument('size');
        if ($size < 1) {
            throw new InvalidArgumentException('Invalid size');
        }
        $args = $this->get_options($input);
        $jwk = Jwk_Factory::create_oct_key($size, $args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}