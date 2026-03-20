<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_bool;
use function is_string;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:generate:from_secret', description: 'Generate an octet key (JWK format) using an existing secret')]
final class Secret_Key_Generator_Command extends Generator_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_argument('secret', Input_Argument::REQUIRED, 'The secret')->add_option('is_b64', 'b', Input_Option::VALUE_NONE, 'Indicates if the secret is Base64 encoded (useful for binary secrets)');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $secret = $input->get_argument('secret');
        if (!is_string($secret)) {
            throw new InvalidArgumentException('Invalid secret');
        }
        $is_bsae64encoded = $input->get_option('is_b64');
        if (!is_bool($is_bsae64encoded)) {
            throw new InvalidArgumentException('Invalid option value for "is_b64"');
        }
        if ($is_bsae64encoded) {
            $secret = base64_decode($secret, true);
        }
        if (!is_string($secret)) {
            throw new InvalidArgumentException('Invalid secret');
        }
        $args = $this->get_options($input);
        $jwk = Jwk_Factory::create_from_secret($secret, $args);
        $this->prepare_json_output($input, $output, $jwk);
        return self::SUCCESS;
    }
}