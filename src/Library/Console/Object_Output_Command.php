<?php

declare (strict_types=1);
namespace Jose\Component\Console;

use Jose\Component\Core\Util\Json_Converter;
use JsonSerializable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
abstract class Object_Output_Command extends Command
{
    protected function prepare_json_output(Input_Interface $input, Output_Interface $output, JsonSerializable $json): void
    {
        $data = Json_Converter::encode($json);
        $output->write($data);
    }
}