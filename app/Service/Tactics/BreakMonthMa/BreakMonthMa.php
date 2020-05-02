<?php

/**
 * 突破月線
 *
 * 1. 今日收盤價大於月均線
 * 2. 昨日收盤價小於等於月均線
 */

namespace App\Service\Tactics\BreakMonthMa;

use App\Service\Tactics\Tactics;

class BreakMonthMa extends Tactics
{
    public function run(string $date)
    {
        $this->date($date);
    }

    /**
     * @return array
     */
    public function param(): array
    {
        return [
            'date' => [0, 1],
            'rules' => [
                // 今日收盤價大於月均線
                0 => [
                    [
                        'where' => 'close',
                        'operator' => '>=',
                        'value' => '20ma',
                    ],
                ],
                // 昨日收盤價小於等於月均線
                1 => [
                    [
                        'where' => 'close',
                        'operator' => '<',
                        'value' => '20ma',
                    ],
                ],
            ],
        ];
    }
}
