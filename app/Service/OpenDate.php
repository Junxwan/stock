<?php

namespace App\Service;

use App\Repository\OpenDateRepository;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class OpenDate
{
    private $close = [
        '2020-01-01',
        '2020-01-02',
        '2020-01-20',
        '2020-01-23',
        '2020-01-24',
        '2020-01-25',
        '2020-01-26',
        '2020-01-27',
        '2020-01-28',
        '2020-01-29',
        '2020-01-30',
        '2020-02-28',
        '2020-04-02',
        '2020-04-03',
        '2020-04-04',
        '2020-05-01',
        '2020-06-25',
        '2020-06-26',
        '2020-10-01',
        '2020-10-02',
        '2020-10-09',
        '2020-10-10',
    ];

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
            $isOpen = $d->isWeekday();
            if (in_array($d->toDateString(), $this->close)) {
                $isOpen = false;
            }

            $insert[] = [
                'date' => $d->toDateString(),
                'week' => $d->weekday(),
                'open' => $isOpen,
            ];
        }

        return $this->repo->batchInsert($insert);
    }
}
