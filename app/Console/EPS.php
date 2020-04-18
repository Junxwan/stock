<?php

namespace App\Console;

use App\Service\EPS\Write;
use App\Service\EPS\Data;
use App\Service\EPS\EPS as Year;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EPS extends Command
{
    /**
     * @var string
     */
    protected $signature = 'eps:save {path} {date}';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        try {
            $date = $this->argument("date");
            $path = $this->argument("path");

            if ($date == 'now') {
                $date = Carbon::now()->year;
            }

            $data = new Data($path, $date);
            $store = new Write($data);

            $store->save([
                new Year($data),
            ], $path . '\\' . $date . '_eps_year.xlsx', $path . '\example.xlsx');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
