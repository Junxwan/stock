<?php

namespace App\Service\Tactics;

class TacticsProfit
{
    /**
     * 買隔日收盤價 再三天後賣出
     */
    const BUY_NEXT_DAY_CLOSE_THREE_SELL = 'buy_next_day_close_three_sell';

    /**
     * @param string $name
     *
     * @return array
     */
    public function param(string $name)
    {
        return collect($this->methods())->where('name', $name)->first();
    }

    /**
     * @return array
     */
    private function methods(): array
    {
        return [
            $this->buyNextDayCloseThreeSell(),
        ];
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
}
