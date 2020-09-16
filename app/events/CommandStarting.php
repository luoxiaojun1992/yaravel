<?php

namespace App\Events;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandStarting
{
    /**
     * The command name.
     *
     * @var string
     */
    public $command;

    /**
     * The console input implementation.
     *
     * @var \Symfony\Component\Console\Input\InputInterface|null
     */
    public $input;

    /**
     * The command output implementation.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface|null
     */
    public $output;

    /**
     * Firing time
     *
     * @var string|null
     */
    public $dateTime;

    /**
     * Create a new event instance.
     *
     * @param  string  $command
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  string $dateTime
     * @return void
     */
    public function __construct($command, InputInterface $input, OutputInterface $output, $dateTime = null)
    {
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;
        $this->dateTime = $dateTime ?: Carbon::now()->toDateTimeString();
    }
}
