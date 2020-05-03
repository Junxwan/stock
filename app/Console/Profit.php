<?php

/**
 * 策略盈虧分析
 */

namespace App\Console;

use App\Service\Tactics\Profit as Tactics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Profit extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tactics:profit {date} {tactics} {type}';

    public function handle()
    {
        try {
            app(Tactics::class)->run(
                $this->argument('date'),
                $this->argument('tactics'),
                $this->argument('type')
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
