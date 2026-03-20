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
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:load:key', description: 'Loads a key from a key file (JWK format)')]
final class Key_File_Loader_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('file', Input_Argument::REQUIRED, 'Filename of the key.')->add_option('secret', 's', Input_Option::VALUE_OPTIONAL, 'Secret if the key is encrypted.');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $file = $input->get_argument('file');
        $password = $input->get_option('secret');
        if (!is_string($file)) {
            throw new InvalidArgumentException('Invalid file');
        }
        if ($password !== null && !is_string($password)) {
            throw new InvalidArgumentException('Invalid secret');
        }
        $args = $this->get_options($input);
        $jwk = Jwk_Factory::create_from_key_file($file, $password, $args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}