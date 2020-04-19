<?php

namespace App\Service;

use App\Repository\OpenDateRepository;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class OpenDate
{
    /**
     * @var OpenDateRepository
     */
    private $repo;

    /**
     * OpenDate constructor.
     *
     * @param OpenDateRepository $repo
     */
    public function __construct(OpenDateRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param string $date
     *
     * @return bool
     */
    public function create(string $year): bool
    {
        $date = Carbon::createFromFormat('Y-m-d', $year . '-01-01');

        $range = CarbonPeriod::create($date->toDateString(), $date->endOfYear()->toDateString());

        foreach ($range as $d) {
            $insert[] = [
                'date' => $d->toDateString(),
                'week' => $d->weekday(),
                'open' => $d->isWeekday(),
            ];
        }

        return $this->repo->batchInsert($insert);
    }
}
