<?php

namespace App\Commands;

class TinkerCommand extends Command
{
    protected $name = 'tinker';

    protected $description = 'Tinker';

    public function handle()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argc'] = 0;
        $argv = [];
        $argc = 0;

        // And go!
        call_user_func(\Psy\bin());
    }
}
