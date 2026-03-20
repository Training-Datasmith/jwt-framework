<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_string;
use Jose\Component\Key_Management\X5u_Factory;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:load:x5u', description: 'Loads a key set from an url.')]
final class X5u_Loader_Command extends Object_Output_Command
{
    public function __construct(private readonly X5u_Factory $x5u_factory, ?string $name = null)
    {
        parent::__construct($name);
    }
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command will try to get a key set from an URL. The distant key set is list of X.509 certificates.')->add_argument('url', Input_Argument::REQUIRED, 'The URL');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $url = $input->get_argument('url');
        if (!is_string($url)) {
            throw new InvalidArgumentException('Invalid URL');
        }
        $result = $this->x5u_factory->load_from_url($url);
        $this->prepare_json_output($input, $output, $result);
        return self::SUCCESS;
    }
}