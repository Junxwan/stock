<?php

if (! function_exists('bandwidth')) {
    /**
     * 帶寬
     *
     * @param float $top
     * @param float $below
     *
     * @return int
     */
    function bandwidth(float $top, float $below): int
    {
        if ($top == 0 && $below == 0) {
            return 0;
        }

        return round((($top / $below) - 1) * 100);
    }
}

if (! function_exists('rank')) {
    /**
     * 位階
     *
     * @param float $top
     * @param float $below
     * @param float $month
     * @param float $price
     *
     * @return object
     */
    function rank(float $top, float $below, float $month, float $price): object
    {
        $rank = new \stdClass();

        $rank->value = 0;
        $rank->direction = 0;
        $rank->topDiffLie = 0;
        $rank->belowDiffLie = 0;

        if ($month < $price) {
            $rank->direction = 1;
            $diff = $top - $price;
            $width = $top - $month;
        } else {
            $rank->direction = -1;
            $diff = $price - $below;
            $width = $month - $below;
        }

        if ($diff > 0) {
            $rank->value = floor(($width - $diff) / ($width / 10)) * $rank->direction;
            $rank->topDiffLie = round((($top / $price) - 1) * 100, 1);
            $rank->belowDiffLie = round((($below / $price) - 1) * 100, 1);
        }

        return $rank;
    }
}

if (! function_exists('countSlopeNum')) {
    /**
     * 計算斜率
     *
     * @param float $value1
     * @param float $value2
     *
     * @return float
     */
    function countSlopeNum(float $value1, float $value2): float
    {
        if ($value1 == 0 || $value2 == 0) {
            return 0;
        }

        $slope = (($value1 / $value2) - 1) * 100;

        $result = round($slope, 1);

        if ($result == 0) {
            $result = $slope > 0 ? 0.1 : -0.1;
        }

        return $result;
    }
}

if (! function_exists('fee')) {
    /**
     * 手續費
     *
     * @param float $price
     *
     * @return float|int
     */
    function fee(float $price)
    {
        $fee = ceil(($price * 1000) * (0.0855 / 100));
        return $fee >= 20 ? $fee : 20;
    }
}

if (! function_exists('tax')) {
    /**
     * 交易稅
     *
     * @param float $price
     *
     * @return float|int
     */
    function tax(float $price)
    {
        return floor(($price * 1000) * (0.3 / 100));
    }
}

if (! function_exists('profit')) {
    /**
     * 盈餘計算
     *
     * @param float $buy
     * @param float $sell
     *
     * @return array
     */
    function profit(float $buy, float $sell)
    {
        $feeb = fee($buy);
        $fees = fee($sell);
        $tax = tax($sell);

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
}
