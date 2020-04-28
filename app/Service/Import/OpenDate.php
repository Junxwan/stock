<?php

namespace App\Service\Import;

use App\Repository\OpenDateRepository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OpenDate extends Import
{
    /**
     * @var OpenDateRepository
     */
    protected $openDateRepo;

    /**
     * OpenDate constructor.
     *
     * @param OpenDateRepository $openDateRepo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(OpenDateRepository $openDateRepo, Xlsx $xlsx)
    {
        parent::__construct($xlsx);
        $this->openDateRepo = $openDateRepo;
    }

    /**
     * @param Collection $data
     *
     * @return bool
     */
    protected function insert(Collection $data): bool
    {
        foreach (array_slice($data->first(), 2) as $date) {
            $d = Carbon::createFromFormat('Ymd', $date);
            $insert[] = [
                'date' => $d->toDateString(),
                'week' => $d->weekday(),
                'open' => true,
            ];
        }

        $result = $this->openDateRepo->batchInsert($insert);
        $saveTotal = 0;

        if ($result) {
            $saveTotal = count($insert);
        }

        $this->info('total: ' . count($insert) . ' save: ' . $saveTotal);

        return $result;
    }
}
