<?php

namespace App\Console;

use App\Service\Xlsx\ToJson;
use Illuminate\Console\Command;

class Convert extends Command
{
    /**
     * @var string
     */
    protected $signature = 'convert:json {date} {path}';

    public function handle()
    {
        app(ToJson::class, [
            'path' => $this->argument("path"),
            'date' => $this->argument("date"),
            'param' => $this->options(),
        ])->toJson();
    }
}
