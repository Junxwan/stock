<?php

namespace App\Console;

use App\Service\Result\Facade;
use Illuminate\Console\Command;

class Result extends Command
{
    /**
     * @var string
     */
    protected $signature = 'data:result {date} {type} {--key_path=}';

    public function handle()
    {
        Facade::save(
            $this->argument("date"),
            $this->argument("type"),
            $this->options()
        );
    }
}
