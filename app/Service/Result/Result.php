<?php

/**
 * 分析個股base
 */

namespace App\Service\Result;

use App\Repository\EPSRepository;
use App\Repository\KeyResultRepository;
use App\Repository\MainRepository;
use App\Repository\OpenDateRepository;
use App\Repository\PointRepository;
use App\Repository\PriceRepository;
use App\Repository\PriceResultRepository;
use App\Repository\StockRepository;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class Result
{
    use InteractsWithIO;

    /**
     * @var PriceRepository
     */
    protected $priceRepo;

    /**
     * @var EPSRepository
     */
    protected $epsRepo;

    /**
     * @var StockRepository
     */
    protected $stockRepo;

    /**
     * @var OpenDateRepository
     */
    protected $openDateRepo;

    /**
     * @var PriceResultRepository
     */
    protected $priceResultRepo;

    /**
     * @var KeyResultRepository
     */
    protected $keyResultRepo;

    /**
     * @var MainRepository
     */
    protected $mainRepo;

    /**
     * @var PointRepository
     */
    protected $pointRepo;

    /**
     * Price constructor.
     *
     * @param StockRepository $stockRepo
     * @param PriceRepository $priceRepo
     * @param EPSRepository $epsRepo
     * @param OpenDateRepository $openDateRepo
     * @param PriceResultRepository $priceResultRepo
     * @param MainRepository $mainRepo
     * @param KeyResultRepository $keyResultRepo
     * @param PointRepository $pointRepo
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        StockRepository $stockRepo,
        PriceRepository $priceRepo,
        EPSRepository $epsRepo,
        OpenDateRepository $openDateRepo,
        PriceResultRepository $priceResultRepo,
        MainRepository $mainRepo,
        KeyResultRepository $keyResultRepo,
        PointRepository $pointRepo
    ) {
        $this->priceRepo = $priceRepo;
        $this->epsRepo = $epsRepo;
        $this->stockRepo = $stockRepo;
        $this->openDateRepo = $openDateRepo;
        $this->priceResultRepo = $priceResultRepo;
        $this->mainRepo = $mainRepo;
        $this->keyResultRepo = $keyResultRepo;
        $this->pointRepo = $pointRepo;

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * @param string $date
     *
     * @return mixed
     */
    abstract public function save(string $date);

    /**
     * 所有股票清單
     *
     * @return \Illuminate\Support\Collection
     */
    protected function stock()
    {
        $stock = collect();
        foreach ($this->stockRepo->all() as $value) {
            $stock->put($value->code, $value);
        }

        if ($stock->isEmpty()) {
            throw new \Exception('not stock');
        }

        return $stock;
    }

    /**
     * 所有分點
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function point()
    {
        $point = collect();
        foreach ($this->pointRepo->all() as $value) {
            $point->put($value->code, $value);
        }

        if ($point->isEmpty()) {
            throw new \Exception('not point');
        }

        return $point;
    }

    /**
     * 某年eps
     *
     * @param string $year
     *
     * @return \Illuminate\Support\Collection
     */
    protected function eps(string $year)
    {
        $eps = collect();
        foreach ($this->epsRepo->year($year) as $value) {
            $eps->put($value->code, $value);
        }

        if ($eps->isEmpty()) {
            throw new \Exception('not eps');
        }

        return $eps;
    }

    /**
     * 昨日價格資料
     *
     * @param string $date
     */
    protected function yesterday(string $date)
    {
        $yesterday = $this->openDateRepo->yesterday($date);
        if ($yesterday == null) {
            throw new \Exception('not ' . $date . '　yesterday');
        }

        $price = collect();
        foreach ($this->priceRepo->date($yesterday->date) as $value) {
            $price->put($value->code, $value);
        }

        if ($price->isEmpty()) {
            throw new \Exception('not ' . $yesterday->date . ' price');
        }

        return $price;
    }

    /**
     * 主力分點進出
     *
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    protected function main(string $date)
    {
        $main = $this->mainRepo->date($date)->groupBy('code');
        $point = $this->point();

        if ($main->isEmpty()) {
            throw new \Exception('not ' . $date . ' main');
        }

        foreach ($main as $stock) {
            $buy = collect();
            $sell = collect();
            foreach ($stock as $value) {
                $d = [
                    'code' => $value->point_code,
                    'name' => $point[$value->point_code]->name,
                    'count' => $value->count,
                    'volume' => $value->volume_ratio,
                ];

                if ($value->count > 0) {
                    $buy->add($d);
                } else {
                    $sell->add($d);
                }
            }

            $main->put($value->code, [
                'buy' => $buy->sortBy('count')->reverse(),
                'sell' => $sell->sortBy('count'),
            ]);
        }

        return $main;
    }

    /**
     * 股票分析結果
     *
     * @param string $date
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function result(string $date)
    {
        $result = collect();
        foreach ($this->priceResultRepo->date($date) as $value) {
            $result->put($value->code, $value);
        }

        return $result;
    }

    /**
     * 個股關鍵分點進出
     *
     * @param string $date
     *
     * @return \Illuminate\Support\Collection
     */
    protected function keyResult(string $date)
    {
        return $this->keyResultRepo->date($date)
            ->groupBy('code');
    }
}
