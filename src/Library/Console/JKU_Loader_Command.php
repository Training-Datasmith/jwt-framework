<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Key_Management\Jku_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:load:jku', description: 'Loads a key set from an url.')]
final class Jku_Loader_Command extends Object_Output_Command
{
    public function __construct(private readonly Jku_Factory $jku_factory, ?string $name = null)
    {
        parent::__construct($name);
    }
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command will try to get a key set from an URL. The distant key set is a JWKSet.')->add_argument('url', Input_Argument::REQUIRED, 'The URL');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $url = $input->get_argument('url');
        if (!is_string($url)) {
            throw new InvalidArgumentException('Invalid URL');
        }
        $result = $this->jku_factory->load_from_url($url);
        $this->prepare_json_output($input, $output, $result);
        return self::SUCCESS;
    }
}