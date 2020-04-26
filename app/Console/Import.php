<?php

namespace App\Console;

use App\Service\Import\Facade;
use Illuminate\Console\Command;

class Import extends Command
{
    /**
     * @var string
     */
    protected $signature = 'stock:import {type} {date} {path} {--year=}';

    public function handle()
    {
        $date = $this->argument("date");
        $path = $this->argument("path");
        $type = $this->argument("type");

        Facade::save($path, $date, $type, $this->options());
    }
}
