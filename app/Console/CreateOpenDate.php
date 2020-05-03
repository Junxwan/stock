<?php

/**
 * 以年為單位建立開市日
 */

namespace App\Console;

use App\Service\OpenDate;
use Illuminate\Console\Command;

class CreateOpenDate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'create:openDate {year}';

    public function handle()
    {
        $result = app(OpenDate::class)->create($this->argument("year"));

        $this->info((bool)$result);
    }
}
