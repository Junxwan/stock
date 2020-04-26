<?php

/**
 * 匯入以年為單位的資料
 */

namespace App\Service\Import\Year;

use App\Exceptions\StockException;
use \App\Service\Import\Import as Base;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

abstract class Import extends Base
{
    /**
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected function insert(Collection $data): bool
    {
        $date = $this->date($data);
        $diff = array_diff_assoc($date->all(), $this->openDateRepo->year($this->year)->pluck('date')->all());

        if (count($diff) > 0) {
            throw new \Exception('date diff: [' . implode(',', $diff) . ']');
        }

        unset($data[0]);

        $insertAllTotal = 0;
        $updateAllTotal = 0;
        $blankTotal = 0;
        $blankAllTotal = 0;

        foreach ($data as $c => $value) {
            $code = $value[0];
            $p = array_slice($value, 2);
            $prices = $this->prices($code, $this->year);
            $insert = [];
            $update = [];

            foreach ($p as $i => $v) {
                if ($v == '') {
                    $blankTotal++;
                    continue;
                }

                $d = $date[$i];
                $price = $prices->get($d);

                if ($price == null) {
                    $insert[] = $this->new(collect([
                        'code' => $code,
                        'date' => $d,
                        $this->key() => $v,
                    ]));
                } elseif ($price[$this->key()] != $v) {
                    $update[] = [
                        'code' => $code,
                        'date' => $d,
                        $this->key() => $v,
                    ];
                }
            }

            $insertTotal = 0;
            if (count($insert) > 0) {
                $result = $this->priceRepo->batchInsert($insert);

                if (! $result) {
                    throw new StockException($code, 'insert error for ' . $d);
                }

                $insertTotal = count($insert);
            }

            $updateTotal = 0;
            if (count($update) > 0) {
                $updateTotal = $this->priceRepo->batchUpdate($code, 'open', $update);

                if ($updateTotal <= 0) {
                    throw new StockException($code, 'update error for ' . $d);
                }
            }

            $this->info(
                'code:' . $code .
                ' total:' . count($p) .
                ' insert:' . $insertTotal .
                ' update:' . $updateTotal .
                ' blank:' . $blankTotal
            );

            $insertAllTotal += $insertTotal;
            $updateAllTotal += $updateTotal;
            $blankAllTotal += $blankTotal;

            $blankTotal = 0;
        }

        $this->info($this->year . ' total: ' . count($date) * $data->count() . ' insert: ' . $insertAllTotal . ' update: ' . $updateAllTotal . ' blank: ' . $blankAllTotal);

        return true;
    }

    /**
     * @param string $code
     * @param string $year
     *
     * @return Collection
     */
    private function prices(string $code, string $year)
    {
        $data = collect();
        foreach ($this->priceRepo->getYear($code, $year) as $value) {
            $data->put($value->date->toDateString(), $value);
        }

        return $data;
    }

    /**
     * @param Collection $value
     *
     * @return array
     */
    private function new(Collection $value)
    {
        return [
            'code' => $value['code'],
            'date' => $value['date'],
            'open' => $value->get('open', 0),
            'close' => $value->get('close', 0),
            'max' => $value->get('max', 0),
            'min' => $value->get('min', 0),
            'increase' => $value->get('increase', 0),
            'amplitude' => $value->get('amplitude', 0),
            'last_year_max' => $value->get('last_year_max', 0),
            'last_year_min' => $value->get('last_year_min', 0),
            'last_year_date' => $value->get('last_year_date', null),
            '5ma' => $value->get('5ma', 0),
            '10ma' => $value->get('10ma', 0),
            '20ma' => $value->get('20ma', 0),
            '60ma' => $value->get('60ma', 0),
            '240ma' => $value->get('240ma', 0),
            '5_stray' => $value->get('5_stray', 0),
            '10_stray' => $value->get('10_stray', 0),
            'month_stray' => $value->get('month_stray', 0),
            'season_stray' => $value->get('season_stray', 0),
            'year_stray' => $value->get('year_stray', 0),
            'main_1' => $value->get('main_1', 0),
            'main_5' => $value->get('main_5', 0),
            'main_10' => $value->get('main_10', 0),
            'main_20' => $value->get('main_20', 0),
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
            'yoy' => $value->get('yoy', 0),
            'mom' => $value->get('mom', 0),
            'financing_maintenance' => $value->get('financing_maintenance', 0),
            'financing_use' => $value->get('financing_use', 0),
            'securities_ratio' => $value->get('securities_ratio', 0),
            'turnover' => $value->get('turnover', 0),
            'net_worth' => $value->get('net_worth', 0),
            'compulsory_replenishment_day' => $value->get('compulsory_replenishment_day', null),
            'main_cost' => $value->get('main_cost', 0),
            'trust_cost' => $value->get('trust_cost', 0),
            'foreign_investment_cost' => $value->get('foreign_investment_cost', 0),
            'self_employed_cost' => $value->get('self_employed_cost', 0),
            'sell_by_coupon' => $value->get('sell_by_coupon', 0),
            'borrowing_the_balance' => $value->get('borrowing_the_balance', 0),
            'debit_balance' => $value->get('stock_exchange_borrowing_balance',
                    0) + $value->get('volume_merchant_balance', 0),
            'stock_exchange_borrowing_balance' => $value->get('stock_exchange_borrowing_balance', 0),
            'volume_merchant_balance' => $value->get('volume_merchant_balance', 0),
            'buy_sell_point_diff' => $value->get('buy_sell_point_diff', 0),
            'buy_sell_main_count' => $value->get('buy_sell_main_count', 0),
            'buy_trading_amount' => $value->get('buy_trading_amount', 0),
            'sell_trading_amount' => $value->get('sell_trading_amount', 0),
        ];
    }

    /**
     * @param Collection $data
     *
     * @return Collection
     */
    private function date(Collection $data)
    {
        $date = collect();
        foreach (array_slice($data->get(0), 2) as $header) {
            $ds = explode(' ', $header);
            $d = Carbon::createFromFormat('Ymd', $ds[0]);
            $date->add($d->toDateString());
        }

        $unique = array_unique($date->all());
        $diff = array_diff_assoc($date->all(), $unique);

        if (count($diff) > 0) {
            throw new \Exception('date repeat: [' . implode(',', $diff) . ']');
        }

        return $date;
    }

    /**
     * @return string
     */
    protected abstract function key();
}
