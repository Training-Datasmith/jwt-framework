<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:generate:ec', description: 'Generate an EC key (JWK format)')]
final class Ec_Key_Generator_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('curve', Input_Argument::REQUIRED, 'Curve of the key.');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $curve = $input->get_argument('curve');
        if (!is_string($curve)) {
            throw new InvalidArgumentException('Invalid curve');
        }
        $args = $this->get_options($input);
        $jwk = Jwk_Factory::create_ec_key($curve, $args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}