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
#[As_Command(name: 'key:load:x509', description: 'Load a key from a X.509 certificate file.')]
final class X509certificate_Loader_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('file', Input_Argument::REQUIRED, 'Filename of the X.509 certificate.');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $file = $input->get_argument('file');
        if (!is_string($file)) {
            throw new InvalidArgumentException('Invalid file');
        }
        $args = [];
        foreach (['use', 'alg'] as $key) {
            $value = $input->get_option($key);
            if ($value !== null) {
                $args[$key] = $value;
            }
        }
        $jwk = Jwk_Factory::create_from_certificate_file($file, $args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}