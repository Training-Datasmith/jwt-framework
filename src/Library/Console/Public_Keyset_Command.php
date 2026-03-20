<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:convert:public', description: 'Convert private keys in a key set into public keys. Symmetric keys (shared keys) are not changed.')]
final class Public_Keyset_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command converts private keys in a key set into public keys.')->add_argument('jwkset', Input_Argument::REQUIRED, 'The JWKSet object');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $jwkset = $this->get_keyset($input);
        $new_jwkset = new Jwk_Set([]);
        foreach ($jwkset->all() as $jwk) {
            $new_jwkset = $new_jwkset->with($jwk->to_public());
        }
        $this->prepare_json_output($input, $output, $new_jwkset);
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
}