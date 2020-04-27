<?php

namespace App\Service\Xlsx;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ToJson extends Xlsx
{
    /**
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function toJson()
    {
        try {
            if ($this->isExists()) {
                return false;
            }

            $data = json_encode($this->getData()->toArray());

            if ($this->isExists()) {
                return false;
            }

            $storage = Storage::getFacadeRoot()->createLocalDriver(['root' => $this->path]);
            $result = $storage->prepend($this->year . '.json', $data);

            if ($result) {
                $this->info($this->year . ' ok');
            } else {
                $this->error($this->year . ' error');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isExists()
    {
        if (File::exists($this->path . '\\' . $this->year . '.json')) {
            $this->info($this->year . '.json' . ' file is exist');
            return true;
        }

        return false;
    }
}
