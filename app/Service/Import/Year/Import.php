<?php

/**
 * 匯入以年為單位的資料
 */

namespace App\Service\Import\Year;

use App\Exceptions\StockException;
use \App\Service\Import\Import as Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Service\Xlsx\Xlsx;

abstract class Import extends Base
{
    /**
     * @var Collection
     */
    protected $openDate;

    /**
     * @var array
     */
    private $rang = [
        '5ma' => 5,
        '10ma' => 10,
        '20ma' => 20,
        '60ma' => 60,
        '120ma' => 120,
        '240ma' => 240,
    ];

    /**
     * Import constructor.
     *
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Xlsx $xlsx)
    {
        parent::__construct($xlsx);
        $this->init();
    }

    /**
     * 初始化
     */
    private function init()
    {
        $date = [];
        foreach ($this->openDateRepo->all()->pluck('date') as $value) {
            $date[] = [
                'date' => $value,
                't' => (int)Carbon::createFromFormat('Y-m-d', $value)->format('Ymd'),
            ];
        }

        $this->openDate = collect($date);
    }

    /**
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected abstract function insert(Collection $data): bool;

    /**
     * 撿查資料日期是否跟某年開市日一致
     *
     * @param Collection $date
     * @param Collection $dates
     */
    protected function checkYearDate(Collection $date, Collection $dates)
    {
        $date->each(function ($value) use ($dates) {
            if ($dates->count() != $value->count()) {
                throw new \Exception('date count is equals ' . $this->year . ' open date ' . $dates->count() . ' count');
            }

            if ($dates->pluck('date')->diff($value)->isNotEmpty()) {
                throw new \Exception('date rang is equals ' . $this->year . ' open date');
            }
        });
    }

    /**
     * @param Collection $date
     */
    protected function checkMonthDate(Collection $date)
    {
        $date->each(function ($value) {
            if ($value->count() != 12) {
                throw new \Exception('month count is equals ' . $this->year . ' 12 month count');
            }

            $month = Carbon::createFromFormat('Ymd', $this->year . '1201');
            foreach ($value as $m) {
                if ($month->format('Y-m') != $m) {
                    throw new \Exception($m . ' month is not equals ' . $month->format('Y-m'));
                }

                $month->subMonth();
            }
        });
    }

    /**
     * @param Collection $data
     *
     * @throws \Exception
     */
    protected function getDate(Collection $data): Collection
    {
        $date = collect();
        foreach ($data as $key => $value) {
            $this->info('read ' . $key . ' ...');
            if (isset($this->rang[$key])) {
                $date->put($key, $this->rangDate($value, $this->rang[$key]));
            } else {
                $date->put($key, $this->date($value));
            }
        }

        return $date;
    }

    /**
     * @param string $code
     * @param string $year
     *
     * @return Collection
     */
    protected function prices(string $code, string $year)
    {
        $data = collect();
        foreach ($this->priceRepo->getYear($code, $year) as $value) {
            $data->put($value->date, $value);
        }

        return $data;
    }

    /**
     * @return Collection
     */
    protected function stocks()
    {
        $data = collect();
        foreach ($this->stockRepo->all() as $value) {
            $data->put($value->code, $value);
        }

        return $data;
    }

    /**
     * @param Collection $value
     *
     * @return array
     */
    protected function new(Collection $value)
    {
        return [
            'code' => $value->get('code', ''),
            'date' => $value->get('date', ''),

            'open' => $value->get('open', 0),
            'close' => $value->get('close', 0),
            'max' => $value->get('max', 0),
            'min' => $value->get('min', 0),

            'increase' => $value->get('increase', 0),
            'amplitude' => $value->get('amplitude', 0),

            'last_year_max' => $value->get('last_year_max', 0),
            'last_year_min' => $value->get('last_year_min', 0),

            '5ma' => $value->get('5ma', 0),
            '10ma' => $value->get('10ma', 0),
            '20ma' => $value->get('20ma', 0),
            '60ma' => $value->get('60ma', 0),
            '120ma' => $value->get('120ma', 0),
            '240ma' => $value->get('240ma', 0),

            '5stray' => $value->get('5stray', 0),
            '10stray' => $value->get('10stray', 0),
            'month_stray' => $value->get('month_stray', 0),
            'season_stray' => $value->get('season_stray', 0),
            'year_stray' => $value->get('year_stray', 0),

            'main1' => $value->get('main1', 0),
            'main5' => $value->get('main5', 0),
            'main10' => $value->get('main10', 0),
            'main20' => $value->get('main20', 0),

            'bb_top' => $value->get('bb_top', 0),
            'bb_below' => $value->get('bb_below', 0),

            'foreign_investment_buy' => $value->get('foreign_investment_buy', 0),
            'foreign_investment_total' => $value->get('foreign_investment_total', 0),
            'foreign_investment_ratio' => $value->get('foreign_investment_ratio', 0),

            'trust_buy' => $value->get('trust_buy', 0),
            'trust_total' => $value->get('trust_total', 0),
            'trust_ratio' => $value->get('trust_ratio', 0),

            'self_employed_buy' => $value->get('self_employed_buy', 0),
            'self_employed_buy_by_self' => $value->get('self_employed_buy_by_self', 0),
            'self_employed_buy_by_hedging' => $value->get('self_employed_buy_by_hedging', 0),
            'self_employed_total' => $value->get('self_employed_total', 0),
            'self_employed_ratio' => $value->get('self_employed_ratio', 0),

            'main_buy_n' => $value->get('main_buy_n', 0),
            'trust_buy_n' => $value->get('trust_buy_n', 0),
            'foreign_investment_buy_n' => $value->get('foreign_investment_buy_n', 0),
            'self_employed_buy_n' => $value->get('self_employed_buy_n', 0),

            'volume' => $value->get('volume', 0),
            'volume20' => $value->get('volume20', 0),
            'stock_trading_volume' => $value->get('stock_trading_volume', 0),
            'credit_trading_volume' => $value->get('credit_trading_volume', 0),
            'turnover' => $value->get('turnover', 0),
            'buy_trading_amount' => $value->get('buy_trading_amount', 0),
            'sell_trading_amount' => $value->get('sell_trading_amount', 0),

            'financing_maintenance' => $value->get('financing_maintenance', 0),
            'financing_use' => $value->get('financing_use', 0),
            'securities_ratio' => $value->get('securities_ratio', 0),

            'net_worth' => $value->get('net_worth', 0),

            'main_cost' => $value->get('main_cost', 0),
            'trust_cost' => $value->get('trust_cost', 0),
            'foreign_investment_cost' => $value->get('foreign_investment_cost', 0),
            'self_employed_cost' => $value->get('self_employed_cost', 0),

            'sell_by_coupon' => $value->get('sell_by_coupon', 0),
            'borrowing_the_balance' => $value->get('borrowing_the_balance', 0),
            'stock_exchange_borrowing_balance' => $value->get('stock_exchange_borrowing_balance', 0),
            'volume_merchant_balance' => $value->get('volume_merchant_balance', 0),

            'buy_sell_point_diff' => $value->get('buy_sell_point_diff', 0),
            'buy_sell_main_count' => $value->get('buy_sell_main_count', 0),
        ];
    }

    /**
     * @param Collection $data
     *
     * @return Collection
     * @throws \Exception
     */
    private function date(Collection $data)
    {
        return $this->parserDate($data, function ($header) {
            $ds = explode(' ', $header);
            return Carbon::createFromFormat('Ymd', $ds[0])->toDateString();
        });
    }

    /**
     * @param Collection $data
     *
     * @return Collection
     * @throws \Exception
     */
    private function yearMonth(Collection $data)
    {
        return $this->parserDate($data, function ($header) {
            $ds = explode(' ', $header);
            return Carbon::createFromFormat('Ymd', $ds[0] . '01')->format('Y-m');
        });
    }

    /**
     * @param Collection $data
     *
     * @return Collection
     * @throws \Exception
     */
    private function year(Collection $data)
    {
        return $this->parserDate($data, function ($header) {
            $ds = explode(' ', $header);
            return $ds[0];
        });
    }

    /**
     * @param Collection $data
     * @param int $rang
     *
     * @return Collection
     * @throws \Exception
     */
    private function rangDate(Collection $data, int $rang)
    {
        return $this->parserDate($data, function ($header) use ($rang) {
            $ds = explode('~', substr($header, 0, 17));

            $r = $this->openDate->where('t', '<=', $ds[1])->slice(0, $rang);

            if ($r->count() != $rang) {
                throw new \Exception('header date rang is not ' . $rang . ' ma');
            }

            if ($r->first()['t'] != $ds[1]) {
                throw new \Exception('header end date is not ' . $rang . ' ma for [' . $header . ']');
            }

            if ($r->last()['t'] != $ds[0]) {
                throw new \Exception('header end date is not ' . $rang . ' ma for [' . $header . ']');
            }

            return Carbon::createFromFormat('Ymd', $ds[1])->toDateString();
        });
    }

    /**
     * 從header解析日期
     *
     * @param Collection $data
     * @param callable|null $callback
     *
     * @return Collection
     * @throws \Exception
     */
    protected function parserDate(Collection $data, callable $callback)
    {
        $date = collect();
        foreach (array_slice($data->get(0), 2) as $header) {
            $date->add($callback($header));
        }

        $unique = array_unique($date->all());
        $diff = array_diff_assoc($date->all(), $unique);

        if (count($diff) > 0) {
            throw new \Exception('date repeat: [' . implode(',', $diff) . ']');
        }

        return $date;
    }
}
