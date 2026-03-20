<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:merge', description: 'Merge several key sets into one.')]
final class Merge_Keyset_Command extends Object_Output_Command
{
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command merges several key sets into one. It is very useful when you generate e.g. RSA, EC and OKP keys and you want only one key set to rule them all.')->add_argument('jwksets', Input_Argument::REQUIRED | Input_Argument::IS_ARRAY, 'The JWKSet objects');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        /** @var string[] $keySets */
        $key_sets = $input->get_argument('jwksets');
        $new_jwkset = new Jwk_Set([]);
        foreach ($key_sets as $key_set) {
            $json = Json_Converter::decode($key_set);
            if (!is_array($json)) {
                throw new InvalidArgumentException('The argument must be a valid JWKSet.');
            }
            $jwkset = Jwk_Set::create_from_key_data($json);
            foreach ($jwkset->all() as $jwk) {
                $new_jwkset = $new_jwkset->with($jwk);
            }
        }
        $this->prepare_json_output($input, $output, $new_jwkset);
        return self::SUCCESS;
    }
}