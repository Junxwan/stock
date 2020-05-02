<?php

/**
 * 突破月線
 *
 * 1. 今日收盤價大於月均線
 * 2. 昨日收盤價小於等於月均線
 */

namespace App\Service\Tactics;

class BreakMonthMa extends Tactics
{
    /**
     * 突破月線
     */
    const BREAK_MONTH_MA = 'BreakMonthMa';

    /**
     * 突破月線且投信買超
     */
    const TRUST_BUY_BREAK_MONTH_MA = 'TrustBuyBreakMonthMa';

    /**
     * 突破月線且外資買超
     */
    const FOREIGN_INVESTMENT_BUY_BREAK_MONTH_MA = 'ForeignInvestmentBuyBreakMonthMa';

    /**
     * @param string $type
     *
     * @return array
     */
    public function param(string $type): array
    {
        switch ($type) {
            case self::BREAK_MONTH_MA:
                return $this->ma();
            case self::TRUST_BUY_BREAK_MONTH_MA:
                return $this->tyBuyMa();
            case self::FOREIGN_INVESTMENT_BUY_BREAK_MONTH_MA:
                return $this->foBuyMa();
            default:
                return [];
        }
    }

    /**
     * @return array
     */
    private function ma()
    {
        return [
            'name' => self::BREAK_MONTH_MA,
            'rules' => [
                // 今日收盤價大於月均線
                [
                    [
                        'where' => 'close',
                        'operator' => '>=',
                        'value' => '20ma',
                    ],
                ],
                // 昨日收盤價小於等於月均線
                [
                    [
                        'where' => 'close',
                        'operator' => '<',
                        'value' => '20ma',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function tyBuyMa()
    {
        return [
            'name' => self::TRUST_BUY_BREAK_MONTH_MA,
            'tactics' => self::BREAK_MONTH_MA,
            'rules' => [
                [
                    [
                        'where' => 'trust_buy',
                        'operator' => '>=',
                        'value' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function foBuyMa()
    {
        return [
            'name' => self::TRUST_BUY_BREAK_MONTH_MA,
            'tactics' => self::BREAK_MONTH_MA,
            'rules' => [
                [
                    'where' => 'foreign_investment_buy',
                    'operator' => '>=',
                    'value' => 1,
                ],
            ],
        ];
    }
}
