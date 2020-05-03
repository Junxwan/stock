<?php

namespace App\Service\Tactics;

use App\Repository\OpenDateRepository;
use App\Repository\PriceRepository;
use App\Repository\TacticsProfitRepository;
use App\Repository\TacticsResultRepository;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Profit
{
    use InteractsWithIO;

    /**
     * 突破月線後 買隔日收盤價 再三天後賣出
     */
    const BUY_NEXT_DAY_CLOSE_THREE_SELL = 'buy_next_day_close_three_sell';

    /**
     * @var PriceRepository
     */
    protected $priceRepo;

    /**
     * @var TacticsResultRepository
     */
    protected $tacticsResultRepo;

    /**
     * @var TacticsProfitRepository
     */
    protected $tacticsProfitRepo;

    /**
     * @var OpenDateRepository
     */
    protected $openDateRepo;

    /**
     * Profit constructor.
     *
     * @param TacticsResultRepository $tacticsResultRepo
     * @param TacticsProfitRepository $tacticsProfitRepo
     * @param PriceRepository $priceRepo
     * @param OpenDateRepository $openDateRepo
     */
    public function __construct(
        TacticsResultRepository $tacticsResultRepo,
        TacticsProfitRepository $tacticsProfitRepo,
        PriceRepository $priceRepo,
        OpenDateRepository $openDateRepo
    ) {
        $this->priceRepo = $priceRepo;
        $this->tacticsResultRepo = $tacticsResultRepo;
        $this->tacticsProfitRepo = $tacticsProfitRepo;
        $this->openDateRepo = $openDateRepo;

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * @param string $date
     * @param string $tactics
     * @param string $type
     */
    public function run(string $date, string $tactics, string $type)
    {
        $param = [];
        switch ($type) {
            case self::BUY_NEXT_DAY_CLOSE_THREE_SELL:
                $param = $this->buyNextDayCloseThreeSell();
                break;
        }

        return $this->runProfit(collect([$date]), $tactics, $param);
    }

    /**
     * @param Collection $dates
     * @param string $tactics
     * @param array $param
     */
    private function runProfit(Collection $dates, string $tactics, array $param)
    {
        foreach ($dates as $date) {
            $openDate = $this->openDateRepo->all()
                ->where('date', '>=', $date)
                ->sortBy('date')
                ->pluck('date');

            $codes = $this->tacticsResultRepo->type($date, $tactics)->pluck('code');

            if ($codes->isEmpty()) {
                continue;
            }

            $inserts = [];
            $start = $param['start'];
            $end = $param['end'];
            $startDate = $openDate[$start['date']];
            $endDate = $openDate[$end['date']];
            $prices = $this->getPriceIn([$startDate, $endDate], $codes->toArray());

            foreach (array_keys($prices->last()) as $code) {
                $startPrice = is_int($start['price']) ? $start['price'] : $prices[$startDate][$code][$start['price']];
                $endPrice = is_int($end['price']) ? $end['price'] : $prices[$endDate][$code][$end['price']];

                $borrowingInterest = 0;
                if ($param['action']) {
                    $buy = $startPrice;
                    $sell = $endPrice;
                } else {
                    $buy = $endPrice;
                    $sell = $startPrice;
                    $borrowingInterest = $this->interest($sell, false);
                }

                $price = $sell - $buy;
                $increase = (($sell / $buy) - 1) * 100;
                [$amount, $return, $feeB, $feeS, $tax] = $this->profit($buy, $sell);

                $inserts[] = [
                    'code' => $code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'start_price' => $startPrice,
                    'end_price' => $endPrice,
                    'price' => round($price, 2),
                    'increase' => round($increase, 2),
                    'action' => $param['action'],
                    'tactics' => $tactics,
                    'type' => $param['name'],
                    'amount' => $amount,
                    'return' => $return,
                    'buy_fee' => $feeB,
                    'sell_fee' => $feeS,
                    'tax' => $tax,
                    'financing_interest' => 0,
                    'borrowing_interest' => $borrowingInterest,
                ];
            }

            $this->log('========================== ' . $date . ' ==========================');

            if (! $this->save($startDate, $tactics, $param['name'], $inserts)) {
                throw new \Exception('date: ' . $date . ' type: ' . $param['name'] . ' tactics: ' . $tactics . ' insert not ok');
            }
        }

        return true;
    }

    /**
     * 盈餘計算
     *
     * @param $buy
     * @param $sell
     *
     * @return array
     */
    private function profit($buy, $sell)
    {
        $feeb = $this->fee($buy);
        $fees = $this->fee($sell);
        $tax = floor(($sell * 1000) * (0.3 / 100));

        $buy = ($buy * 1000 + $feeb);
        $sell = $sell * 1000 - $fees - $tax;
        $amount = $sell - $buy;
        $return = round(($amount / $buy) * 100, 2);

        return [
            $amount,
            $return,
            $feeb,
            $fees,
            $tax,
        ];
    }

    /**
     * 融資融卷手續費(融資未算)
     *
     * @param $price
     * @param int $type
     *
     * @return float|int
     */
    private function interest($price, int $type)
    {
        $price *= 1000;

        if ($type) {
            return 0;
        }

        return floor($price * 0.0008);
    }

    /**
     * @param $price
     *
     * @return float|int
     */
    private function fee($price)
    {
        $fee = ceil(($price * 1000) * (0.1425 / 100));
        return $fee >= 20 ? $fee : 20;
    }

    /**
     * @param string $startDate
     * @param string $tactics
     * @param string $type
     * @param array $inserts
     *
     * @return bool
     */
    private function save(string $startDate, string $tactics, string $type, array $inserts)
    {
        $exist = [];
        $total = count($inserts);
        $insertTotal = 0;
        foreach ($this->tacticsProfitRepo->type($startDate, $tactics, $type) as $value) {
            $exist[$value->code][$value->action][$value->end_date] = true;
        }

        foreach ($inserts as $i => $value) {
            if (isset($exist[$value['code']][$value['action']][$value['end_date']])) {
                unset($inserts[$i]);
            }
        }

        $result = true;
        if (count($inserts) > 0) {
            $result = $this->tacticsProfitRepo->batchInsert($inserts);

            if ($result) {
                $insertTotal = count($inserts);
            }
        }

        $this->log('total: ' . $total . ' insert: ' . $insertTotal);

        return $result;
    }

    /**
     * @param array $dates
     * @param array $codes
     *
     * @return Collection
     */
    private function getPriceIn(array $dates, array $codes)
    {
        $prices = [];
        foreach ($this->priceRepo->getIn($dates, $codes) as $value) {
            $prices[$value->date][$value->code] = $value;
        }

        return collect($prices);
    }

    /**
     * @return array
     */
    private function buyNextDayCloseThreeSell()
    {
        return [
            'name' => self::BUY_NEXT_DAY_CLOSE_THREE_SELL,
            'action' => true,
            'start' => [
                'date' => 1,
                'price' => 'close',
            ],
            'end' => [
                'date' => 4,
                'price' => 'close',
            ],
        ];
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
