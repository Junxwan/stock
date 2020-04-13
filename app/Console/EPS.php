<?php

namespace App\Console;

use App\Service\EPS as Data;
use App\Service\Price;
use Illuminate\Console\Command;
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

            $price = new Price($path, $date);
            $price->read();

            $data = new Data($path, $date, $price);
            $data->read();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
