<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use InvalidArgumentException;
use function is_array;
use function is_string;
use Jose\Component\Core\Jwk_Set;
use Jose\Component\Core\Util\Json_Converter;
use Jose\Component\Key_Management\Analyzer\Key_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Keyset_Analyzer_Manager;
use Jose\Component\Key_Management\Analyzer\Message_Bag;
use Override;
use function sprintf;
use Symfony\Component\Console\Attribute\As_Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\Output_Formatter_Style;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
#[As_Command(name: 'keyset:analyze', description: 'JWKSet quality analyzer.')]
final class Keyset_Analyzer_Command extends Command
{
    public function __construct(private readonly Keyset_Analyzer_Manager $keyset_analyzer_manager, private readonly Key_Analyzer_Manager $key_analyzer_manager, ?string $name = null)
    {
        parent::__construct($name);
    }
    #[Override]
    protected function configure(): void
    {
        parent::configure();
        $this->set_help('This command will analyze a JWKSet object and find security issues.')->add_argument('jwkset', Input_Argument::REQUIRED, 'The JWKSet object');
    }
    #[Override]
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $output->get_formatter()->set_style('success', new Output_Formatter_Style('white', 'green'));
        $output->get_formatter()->set_style('high', new Output_Formatter_Style('white', 'red', ['bold']));
        $output->get_formatter()->set_style('medium', new Output_Formatter_Style('yellow'));
        $output->get_formatter()->set_style('low', new Output_Formatter_Style('blue'));
        $jwkset = $this->get_keyset($input);
        $messages = $this->keyset_analyzer_manager->analyze($jwkset);
        $this->show_messages($messages, $output);
        foreach ($jwkset as $kid => $jwk) {
            $output->writeln(sprintf('Analysing key with index/kid "%s"', $kid));
            $messages = $this->key_analyzer_manager->analyze($jwk);
            $this->show_messages($messages, $output);
        }
        return self::SUCCESS;
    }
    private function show_messages(Message_Bag $messages, Output_Interface $output): void
    {
        if ($messages->count() === 0) {
            $output->writeln('    <success>All good! No issue found.</success>');
        } else {
            foreach ($messages->all() as $message) {
                $output->writeln('    <' . $message->get_severity() . '>* ' . $message->get_message() . '</' . $message->get_severity() . '>');
            }
        }
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