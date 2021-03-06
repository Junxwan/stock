<?php

namespace App\Service\Tactics;

use Closure;
use App\Exceptions\StockException;
use App\Repository\OpenDateRepository;
use App\Repository\PriceRepository;
use App\Repository\StockRepository;
use App\Repository\TacticsResultRepository;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Tactics
{
    use InteractsWithIO;

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
     * @var TacticsResultRepository
     */
    protected $tacticsResultRepo;

    /**
     * @var array
     */
    private $param = [];

    /**
     * @var array
     */
    private $type = [
        BreakMonthMa::class,
        FallBelowMonthMa::class,
    ];

    /**
     * BreakMonthMa constructor.
     *
     * @param PriceRepository $priceRepo
     * @param OpenDateRepository $openDateRepo
     * @param StockRepository $stockRepo
     * @param TacticsResultRepository $tacticsResultRepo
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        PriceRepository $priceRepo,
        OpenDateRepository $openDateRepo,
        StockRepository $stockRepo,
        TacticsResultRepository $tacticsResultRepo
    ) {
        $this->priceRepo = $priceRepo;
        $this->openDateRepo = $openDateRepo;
        $this->stockRepo = $stockRepo;
        $this->tacticsResultRepo = $tacticsResultRepo;

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * @param string $date
     * @param string $type
     *
     * @throws StockException
     */
    public function run(string $date, string $type)
    {
        $this->param = $this->param($type);

        if ($this->param['name'] != $type) {
            throw new \Exception('[' . $this->param['name'] . '] name is not ' . $type);
        }

        $this->log('=========================== ' . $this->param['name'] . ' =========================== ');

        if (isset($this->param['tactics'])) {
            $this->runByTactics($date, $this->param['tactics']);
        } else {
            $this->runByData($date);
        }
    }

    /**
     * @param string $date
     *
     * @return array
     * @throws StockException
     */
    private function runByData(string $date)
    {
        if (strlen($date) == 4) {
            return $this->yearByDate($date);
        }

        return $this->dateByDate($date);
    }

    /**
     * @param string $date
     * @param string $tactics
     *
     * @return array
     * @throws StockException
     */
    private function runByTactics(string $date, string $tactics)
    {
        $f = function ($date, $tactics) {
            return $this->date($date, function ($openDate) use ($date, $tactics) {
                $codes = $this->tacticsResultRepo->type($date, $tactics)
                    ->pluck('code')
                    ->toArray();

                return $this->priceInCodes($openDate->pluck('date')->toArray(), $codes);
            });
        };

        if (strlen($date) != 4) {
            return $f($date, $tactics);
        }

        return $this->year($date, function ($date) use ($f, $tactics) {
            return $f($date, $tactics);
        });
    }

    /**
     * @param string $date
     * @param Closure $closure
     *
     * @return array
     * @throws StockException
     */
    private function date(string $date, Closure $closure)
    {
        if (isset($this->param['maxDate'])) {
            $len = $this->param['maxDate'] + 1;
        } else {
            $len = last(array_keys($this->param['rules'])) + 1;
        }

        $openDate = $this->openDateRepo->all()
            ->where('date', '<=', $date)
            ->slice(0, $len);

        return $this->doDate($date, $openDate, $closure($openDate));
    }

    /**
     * @param string $year
     * @param Closure $closure
     *
     * @return array
     * @throws \Exception
     */
    private function year(string $year, Closure $closure)
    {
        $result = [];
        foreach ($this->dateRangeByYear($year) as $date) {
            $codes = $closure($date);
            $count = $this->tacticsResultRepo->type($date, $this->param['name'])->count();

            if ($count != count($codes)) {
                throw new \Exception('date: ' . $date . ' result is [' . $count . ']' . ' not ' . count($codes));
            }

            $result[$date] = $codes;
        }

        return $result;
    }

    /**
     * @param string $date
     *
     * @return array
     * @throws StockException
     */
    private function dateByDate(string $date)
    {
        return $this->date($date, function ($openDate) {
            return $this->priceInRange([$openDate->last()->date, $openDate->first()->date]);
        });
    }

    /**
     * @param string $year
     *
     * @return array
     * @throws StockException
     */
    private function yearByDate(string $year)
    {
        return $this->year($year, function ($date) {
            return $this->dateByDate($date);
        });
    }

    /**
     * @param array $dates
     * @param Collection $openDate
     * @param string $date
     *
     * @return array
     * @throws StockException
     */
    private function doDate(string $date, Collection $openDate, Collection $prices)
    {
        $this->log('================================================');
        $this->log('date: ' . $date . ' ...');

        $codes = $this->runResult($openDate->pluck('date'), $prices);

        if (! $this->save($codes, $date, $this->param['name'])) {
            throw new \Exception('[' . $date . '] save not ok: ' . implode(',', $codes));
        }

        $this->log('date result: ' . $date . ' ok');

        return $codes;
    }

    /**
     * @param Collection $dates
     * @param Collection $prices
     *
     * @return array
     * @throws StockException
     */
    private function runResult(Collection $dates, Collection $prices)
    {
        $result = [];
        foreach ($prices->keys() as $code) {
            if ($this->runRule($dates, $prices[$code], $this->param['rules'])) {
                $result[] = $code;
            }
        }

        return $result;
    }

    /**
     * @param Collection $dates
     * @param array $prices
     * @param array $ruleAry
     *
     * @return bool
     */
    private function runRule(Collection $dates, array $prices, array $ruleAry)
    {
        foreach ($ruleAry as $i => $rules) {
            $date = $dates[$i];
            if (! isset($prices[$date])) {
                return false;
            }

            $price = $prices[$date];

            foreach ($rules as $rule) {
                $r = $rule['value'];
                switch (gettype($r)) {
                    case 'int':
                        $value = $r;
                        break;
                    case 'string':
                        $value = $price[$r];
                        break;
                    case 'array':
                        $value = $prices[$dates[$r['i']]][$r['v']];
                        break;
                    default:
                        return false;
                        break;
                }

                $result = $this->operatorForWhere($price[$rule['where']], $rule['operator'], $value);

                if (! $result) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws \Exception
     */
    public function param(string $type)
    {
        foreach ($this->type as $class) {
            $instance = app($class);
            if (is_int(array_search($type, $instance->name()))) {
                return $instance->param($type);
            }
        }

        throw new \Exception('param is empty');
    }

    /**
     * @param string $dates
     *
     * @return Collection
     */
    private function priceInRange(array $dates)
    {
        $data = [];
        foreach ($this->priceRepo->dateRange($dates) as $value) {
            $data[$value->code][$value->date] = $value;
        }

        return collect($data);
    }

    /**
     * @param array $dates
     * @param array $codes
     *
     * @return Collection
     */
    private function priceInCodes(array $dates, array $codes)
    {
        $data = [];
        foreach ($this->priceRepo->getIn($dates, $codes) as $value) {
            $data[$value->code][$value->date] = $value;
        }

        return collect($data);
    }

    /**
     * @param array $code
     * @param string $type
     *
     * @return bool
     */
    private function save(array $codes, string $date, string $type): bool
    {
        $exist = [];
        foreach ($this->tacticsResultRepo->type($date, $type) as $value) {
            $exist[$value->code] = $value;
        }

        $inserts = [];
        foreach ($codes as $code) {
            if (isset($exist[$code])) {
                continue;
            }

            $inserts[] = [
                'code' => $code,
                'date' => $date,
                'type' => $type,
            ];
        }

        $result = true;
        $insertTotal = 0;
        if (count($inserts) > 0) {
            if ($this->tacticsResultRepo->batchInsert($inserts)) {
                $insertTotal = count($inserts);
            } else {
                $result = false;
            }
        }

        $this->log($date . ' total: ' . count($codes) . ' insert: ' . $insertTotal);

        return $result;
    }

    /**
     * @param string $year
     *
     * @return Collection
     */
    private function dateRangeByYear(string $year)
    {
        return $this->openDateRepo->all()
            ->whereBetween('date', [$year . '-01-01', $year . '-12-31'])
            ->pluck('date');
    }

    /**
     * @param $retrieved
     * @param $operator
     * @param $value
     *
     * @return bool
     */
    private function operatorForWhere($retrieved, $operator, $value)
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

    /**
     * @param string $message
     */
    protected function log(string $message)
    {
        $this->info($message);
        Log::info($message);
    }
}
