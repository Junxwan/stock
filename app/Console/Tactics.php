<?php

/**
 * 策略分析
 */

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Tactics extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tactics:run {type} {date} {tactics}';

    public function handle()
    {
        try {
            $tactics = app('App\Service\Tactics\\' . $this->argument('type'));
            $tactics->run($this->argument('date'), $this->argument('tactics'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
