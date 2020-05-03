<?php

/**
 * 策略分析
 */

namespace App\Console;

use App\Service\Tactics\Tactics as Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Tactics extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tactics:run {date} {tactics}';

    public function handle()
    {
        try {
            app(Service::class)->run(
                $this->argument('date'),
                $this->argument('tactics')
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
