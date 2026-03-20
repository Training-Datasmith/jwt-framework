<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_bool;
use Jose\Component\Core\Util\Base64url_Safe;
use Jose\Component\Key_Management\Jwk_Factory;
use Override;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
abstract class Generator_Command extends Object_Output_Command
{
    #[Override]
    public function is_enabled(): bool
    {
        return class_exists(Jwk_Factory::class);
    }
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->add_option('use', 'u', Input_Option::VALUE_OPTIONAL, 'Usage of the key. Must be either "sig" or "enc".')->add_option('alg', 'a', Input_Option::VALUE_OPTIONAL, 'Algorithm for the key.')->add_option('random_id', null, Input_Option::VALUE_NONE, 'If this option is set, a random key ID (kid) will be generated.');
    }
    protected function get_options(Input_Interface $input): array
    {
        $args = [];
        $use_random_id = $input->get_option('random_id');
        if (!is_bool($use_random_id)) {
            throw new InvalidArgumentException('Invalid value for option "random_id"');
        }
        if ($use_random_id) {
            $args['kid'] = $this->generate_key_id();
        }
        foreach (['use', 'alg'] as $key) {
            $value = $input->get_option($key);
            if ($value !== null) {
                $args[$key] = $value;
            }
        }
        return $args;
    }
    private function generate_key_id(): string
    {
        return Base64url_Safe::encode(random_bytes(32));
    }
}