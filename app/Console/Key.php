<?php


namespace App\Console;

use App\Service\Export\Data;
use Exception;
use App\Service\Export\Buy;
use App\Service\Export\Xlsx;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Key extends Command
{
    /**
     * @var string
     */
    protected $signature = 'export:key {date} {path}';

    public function handle()
    {
        try {
            $date = $this->argument("date");
            $path = $this->argument("path");

            $write = new Xlsx(app(Data::class));
            $write->save([
                Buy::class,
            ], $date, $path);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
