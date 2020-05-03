<?php

/**
 * 跌破月線
 */

namespace App\Service\Tactics;

class FallBelowMonthMa extends Base
{
    /**
     * 跌破月線
     */
    const FALL_BELOW_MONTH_MA = 'fall_below_month_ma';

    /**
     * 跌破月線且投信賣超
     */
    const TRUST_BUY_FALL_BELOW_MONTH_MA = 'trust_buy_fall_below_month_ma';

    /**
     * 跌破月線且外資賣超
     */
    const FOREIGN_INVESTMENT_BUY_FALL_BELOW_MONTH_MA = 'foreign_investment_buy_fall_below_month_ma';

    /**
     * 跌破月線且外資與投信賣超
     */
    const FOREIGN_INVESTMENT_AND_TRUST_BUY_FALL_BELOW_MONTH_MA = 'foreign_investment_and_trust_buy_fall_below_month_ma';

    /**
     * @return array
     */
    public function name(): array
    {
        return [
            self::FALL_BELOW_MONTH_MA,
            self::TRUST_BUY_FALL_BELOW_MONTH_MA,
            self::FOREIGN_INVESTMENT_BUY_FALL_BELOW_MONTH_MA,
            self::FOREIGN_INVESTMENT_AND_TRUST_BUY_FALL_BELOW_MONTH_MA,
        ];
    }

    /**
     * @return array
     */
    protected function methods(): array
    {
        return [
            $this->ma(),
            $this->tyBuyMa(),
            $this->foBuyMa(),
            $this->tyAndFoBuyMa(),
        ];
    }

    /**
     * 跌破月線
     *
     * 1. 今日收盤價大於月均線
     * 2. 昨日收盤價小於等於月均線
     *
     * @return array
     */
    private function ma()
    {
        return [
            'name' => self::FALL_BELOW_MONTH_MA,
            'rules' => [
                [
                    [
                        // 今日收盤價小於月均線
                        'where' => 'close',
                        'operator' => '<',
                        'value' => '20ma',
                    ],
                ],

                [
                    [
                        // 昨日收盤價大於等於月均線
                        'where' => 'close',
                        'operator' => '>=',
                        'value' => '20ma',
                    ],
                ],
            ],
        ];
    }

    /**
     * 跌破月線且投信賣超
     *
     * 1. 突破月線
     * 2. 投信賣超小於-1張
     *
     * @return array
     */
    private function tyBuyMa()
    {
        return [
            'name' => self::TRUST_BUY_FALL_BELOW_MONTH_MA,
            'tactics' => self::FALL_BELOW_MONTH_MA,
            'rules' => [
                [
                    [
                        // 投信賣超大於-1張
                        'where' => 'trust_buy',
                        'operator' => '<',
                        'value' => -1,
                    ],
                ],
            ],
        ];
    }

    /**
     * 跌破月線且外資賣超
     *
     * 1. 突破月線
     * 2. 外資賣超小於-1張
     *
     * @return array
     */
    private function foBuyMa()
    {
        return [
            'name' => self::FOREIGN_INVESTMENT_BUY_FALL_BELOW_MONTH_MA,
            'tactics' => self::FALL_BELOW_MONTH_MA,
            'rules' => [
                [
                    [
                        // 外資賣超大於-1張
                        'where' => 'foreign_investment_buy',
                        'operator' => '<',
                        'value' => -1,
                    ],
                ],
            ],
        ];
    }

    /**
     * 跌破月線且外資與投信賣超
     *
     * 1. 跌破月線
     * 2. 外資賣超小於-1張
     * 3. 投信賣超小於-1張
     *
     * @return array
     */
    private function tyAndFoBuyMa()
    {
        return [
            'name' => self::FOREIGN_INVESTMENT_AND_TRUST_BUY_FALL_BELOW_MONTH_MA,
            'tactics' => self::TRUST_BUY_FALL_BELOW_MONTH_MA,
            'rules' => [
                [
                    [
                        // 外資賣超大於-1張
                        'where' => 'foreign_investment_buy',
                        'operator' => '<',
                        'value' => -1,
                    ],
                ],
            ],
        ];
    }
}
