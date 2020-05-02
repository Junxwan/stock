<?php

namespace App\Service\Tactics;

use App\Repository\OpenDateRepository;
use App\Repository\PriceRepository;
use App\Repository\StockRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

abstract class Tactics
{
    /**
     * @var PriceRepository
     */
    protected $priceRepo;

    /**
     * @var OpenDateRepository
     */
    protected $openDateRepo;

    /**
     * @var StockRepository
     */
    protected $stockRepo;

    /**
     * @var Carbon
     */
    protected $date;

    /**
     * BreakMonthMa constructor.
     *
     * @param PriceRepository $priceRepo
     * @param OpenDateRepository $openDateRepo
     * @param StockRepository $stockRepo
     */
    public function __construct(
        PriceRepository $priceRepo,
        OpenDateRepository $openDateRepo,
        StockRepository $stockRepo
    ) {
        $this->priceRepo = $priceRepo;
        $this->openDateRepo = $openDateRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * @param string $date
     */
    public abstract function run(string $date);

    /**
     * @param string $date
     *
     * @return array
     */
    protected function date(string $date)
    {
        $dates = [];
        $param = $this->param();
        $openDate = $this->openDateRepo->all()->where('date', '<=', $date);
        $openDateOffset = $openDate->slice(0, 1)->keys()[0];

        foreach ($param['date'] as $d) {
            $dates[] = $openDate[$openDateOffset + $d]->date;
        }

        $result = [];
        $prices = $this->priceInDate($dates);
        foreach ($prices->last() as $p) {
            if ($this->runRule($prices, $p->code, $dates, $param['rules'])) {
                $result[] = $p->code;
            }
        }

        return $result;
    }

    /**
     * @param Collection $prices
     * @param string $code
     * @param array $dates
     * @param array $ruleAry
     *
     * @return bool
     */
    protected function runRule(Collection $prices, string $code, array $dates, array $ruleAry)
    {
        foreach ($ruleAry as $i => $rules) {
            $price = $prices[$dates[$i]][$code];

            foreach ($rules as $rule) {
                $result = $this->operatorForWhere(
                    $price[$rule['where']],
                    $rule['operator'],
                    $price[$rule['value']]
                );

                if (! $result) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $date
     *
     * @return array
     */
    public abstract function param(): array;

    /**
     * @param array $dates
     *
     * @return \Illuminate\Support\Collection
     */
    protected function priceInDate(array $dates)
    {
        $data = [];
        foreach ($this->priceRepo->dates($dates) as $value) {
            $data[$value->date->toDateString()][$value->code] = $value;
        }

        return collect($data);
    }

    /**
     * @param $retrieved
     * @param $operator
     * @param $value
     *
     * @return bool
     */
    protected function operatorForWhere($retrieved, $operator, $value)
    {
        switch ($operator) {
            case '=':
            case '==':
                return $retrieved == $value;
            case '!=':
            case '<>':
                return $retrieved != $value;
            case '<':
                return $retrieved < $value;
            case '>':
                return $retrieved > $value;
            case '<=':
                return $retrieved <= $value;
            case '>=':
                return $retrieved >= $value;
            case '===':
                return $retrieved === $value;
            case '!==':
                return $retrieved !== $value;
            default:
                return false;
        }
    }
}
