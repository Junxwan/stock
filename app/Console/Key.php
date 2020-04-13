<?php


namespace App\Console;

use App\Service\Data;
use App\Service\Key\All;
use App\Service\Key\Buy;
use App\Service\Key\Sell;
use App\Service\Key\Write;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Key extends Command
{
    /**
     * @var string
     */
    protected $signature = 'key:select {key_path} {date} {path}';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        try {
            $date = $this->argument("date");
            $path = $this->argument("path");
            $keyPath = $this->argument("key_path");

            $data = new Data($date, $path, $keyPath);
            $store = new Write($data);
            $store->save([
                new Buy($data),
                new Sell($data),
                new All($data),
            ], $data->getResultPath() . '\\' . $date . '.xlsx', $data->getResultPath() . '\example.xlsx');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
