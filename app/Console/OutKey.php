<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OutKey extends Command
{
    /**
     * @var string
     */
    protected $signature = 'key:out {key_path} {day_path}';

    public function handle()
    {
        $keyPath = $this->argument("key_path");
        $dayPath = $this->argument("day_path");

        try {
            $spreadsheet = IOFactory::load($keyPath);
            $data = $spreadsheet->getSheet(3)->toArray();
            unset($data[0]);

            $codes = collect($data)->where("2", "!=", null)->pluck("0")->toArray();
            $out = implode(";", $codes);

            File::put($dayPath . '/keyOut_' . count($codes) . '.txt', $out);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}
