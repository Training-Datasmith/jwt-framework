<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:generate:ec', description: 'Generate an EC key set (JWKSet format)')]
final class Ec_Keyset_Generator_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('quantity', Input_Argument::REQUIRED, 'Quantity of keys in the key set.')->add_argument('curve', Input_Argument::REQUIRED, 'Curve of the keys.');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $quantity = (int) $input->get_argument('quantity');
        if ($quantity < 1) {
            throw new InvalidArgumentException('Invalid quantity');
        }
        $curve = $input->get_argument('curve');
        if (!is_string($curve)) {
            throw new InvalidArgumentException('Invalid curve');
        }
        $keyset = new Jwk_Set([]);
        for ($i = 0; $i < $quantity; ++$i) {
            $args = $this->get_options($input);
            $keyset = $keyset->with(Jwk_Factory::create_ec_key($curve, $args));
        }
        $this->prepare_json_output($input, $output, $keyset);
        return self::SUCCESS;
    }
}