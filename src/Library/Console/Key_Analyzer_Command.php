<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Key_Management\Analyzer\Key_Analyzer_Manager;
use Override;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'key:analyze', description: 'JWK quality analyzer.')]
final class Key_Analyzer_Command extends Command
{
    public function __construct(private readonly Key_Analyzer_Manager $analyzer_manager, ?string $name = null)
    {
        parent::__construct($name);
    }
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command will analyze a JWK object and find security issues.')->add_argument('jwk', Input_Argument::REQUIRED, 'The JWK object');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $output->get_formatter()->set_style('success', new Output_Formatter_Style('white', 'green'));
        $output->get_formatter()->set_style('high', new Output_Formatter_Style('white', 'red', ['bold']));
        $output->get_formatter()->set_style('medium', new Output_Formatter_Style('yellow'));
        $output->get_formatter()->set_style('low', new Output_Formatter_Style('blue'));
        $jwk = $this->get_key($input);
        $result = $this->analyzer_manager->analyze($jwk);
        if ($result->count() === 0) {
            $output->writeln('<success>All good! No issue found.</success>');
        } else {
            foreach ($result->all() as $message) {
                $output->writeln('<' . $message->get_severity() . '>* ' . $message->get_message() . '</' . $message->get_severity() . '>');
            }
        }
        return self::SUCCESS;
    }
    private function get_key(Input_Interface $input): JWK
    {
        $jwk = $input->get_argument('jwk');
        if (!is_string($jwk)) {
            throw new InvalidArgumentException('Invalid JWK');
        }
        $json = Json_Converter::decode($jwk);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid JWK.');
        }
        return new JWK($json);
    }
}