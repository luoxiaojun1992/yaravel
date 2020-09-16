<?php

namespace App\Services\Console;

use App\Events\CommandFinished;
use App\Events\CommandStarting;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct(config('app.name'), framework_version());

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $commandName = $this->getCommandName(
            $input = $input ?: new ArgvInput()
        );

        \Event::fire(
            new CommandStarting(
                $commandName, $input, $output = $output ?: new ConsoleOutput()
            )
        );

        $exitCode = parent::run($input, $output);

        \Event::fire(
            new CommandFinished($commandName, $input, $output, $exitCode)
        );

        return $exitCode;
    }
}
