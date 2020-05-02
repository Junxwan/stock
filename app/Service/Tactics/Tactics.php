<?php

namespace App\Service\Tactics;

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

abstract class Tactics
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
     *
     * @throws \App\Exceptions\StockException
     */
    public function run(string $date)
    {
        if (strlen($date) == 4) {
            $this->year($date);
        } else {
            $this->date($date);
        }
    }

    /**
     * @param string $date
     *
     * @return array
     * @throws StockException
     */
    protected function date(string $date)
    {
        $openDate = $this->openDateRepo->all()
            ->where('date', '<=', $date)
            ->slice(0, $this->max() + 1);

        return $this->doDate(
            $date,
            $openDate,
            $this->priceInRange([$openDate->last()->date, $openDate->first()->date])
        );
    }

    /**
     * @param string $year
     *
     * @return array
     * @throws StockException
     */
    protected function year(string $year)
    {
        $openDate = $this->openDateRepo->all()
            ->whereBetween('date', [$year . '-01-01', $year . '-12-31'])
            ->pluck('date');

        $result = [];
        foreach ($openDate as $date) {
            $codes = $this->date($date);
            $count = $this->tacticsResultRepo->type($date, $this->type())->count();

            if ($count != count($codes)) {
                throw new \Exception('date: ' . $date . ' result is [' . $count . ']' . ' not ' . count($codes));
            }

            $result[$date] = $codes;
        }

        return $result;
    }

    /**
     * @param array $dates
     * @param Collection $openDate
     * @param string $date
     *
     * @return array
     * @throws StockException
     */
    protected function doDate(string $date, Collection $openDate, Collection $prices)
    {
        $this->log('================================================');
        $this->log('date: ' . $date . ' ...');

        $codes = $this->runResult($openDate->pluck('date'), $prices);

        if (! $this->save($codes, $date, $this->type())) {
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
    protected function runResult(Collection $dates, Collection $prices)
    {
        $result = [];
        $param = $this->param();
        foreach ($prices->keys() as $code) {
            if ($this->runRule($dates, $prices[$code], $param['rules'])) {
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
    protected function runRule(Collection $dates, array $prices, array $ruleAry)
    {
        foreach ($ruleAry as $i => $rules) {
            $date = $dates[$i];
            if (! isset($prices[$date])) {
                return false;
            }

            $price = $prices[$date];

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
     * @param string $dates
     *
     * @return Collection
     */
    protected function priceInRange(array $dates)
    {
        $data = [];
        foreach ($this->priceRepo->dateRange($dates) as $value) {
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
    protected function save(array $codes, string $date, string $type): bool
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

    /**
     * @return mixed
     */
    protected function max()
    {
        return last(array_keys($this->param()['rules']));
    }

    /**
     * @return string
     */
    public abstract function type(): string;

    /**
     * @param string $message
     */
    protected function log(string $message)
    {
        $this->info($message);
        Log::info($message);
    }
}
