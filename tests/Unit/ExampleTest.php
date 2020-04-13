<?php

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testKeySelect()
    {
        Artisan::call("key:select", [
            "key_path" => 'C:\Users\junx\Desktop\Finance\transaction.xlsx',
            "date" => '2020-04-10',
            "path" => 'C:\Users\junx\Desktop\Finance\data',
        ]);

        // php artisan key:select C:\Users\junx\Desktop\Finance\key\key_2020_02_05.xlsx C:\Users\junx\Desktop\Finance\key\day\2020-02-27.xls C:\Users\junx\Desktop\Finance\key\result
    }

    public function testEpsSave()
    {
        Artisan::call("eps:save", [
            "path" => 'C:\Users\junx\Desktop\Finance\data',
            "date" => '2019',
        ]);

        // php artisan key:select C:\Users\junx\Desktop\Finance\key\key_2020_02_05.xlsx C:\Users\junx\Desktop\Finance\key\day\2020-02-27.xls C:\Users\junx\Desktop\Finance\key\result
    }

    public function testKeyOut()
    {
        Artisan::call("key:out", [
            "key_path" => 'C:\Users\junx\Desktop\Finance\transaction.xlsx',
            "day_path" => 'C:\Users\junx\Desktop\Finance\key',
        ]);

        // php artisan key:out C:\Users\junx\Desktop\Finance\transaction.xlsx C:\Users\junx\Desktop\Finance\key
    }

    public function testS()
    {
        $top = 20;
        $below = 0;
        $month = 10;

        //        $rank = $this->rank($top, $below, $month, 10.5);
        //
        //        $this->assertEquals(0, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 11);
        //
        //        $this->assertEquals(1, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 14.5);
        //
        //        $this->assertEquals(4, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 18);
        //
        //        $this->assertEquals(8, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 19.5);
        //
        //        $this->assertEquals(9, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 20);
        //
        //        $this->assertEquals(10, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 20.5);
        //
        //        $this->assertEquals(10, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 21);
        //
        //        $this->assertEquals(11, $rank->value);
        //        $this->assertEquals(1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 9.5);
        //
        //        $this->assertEquals(0, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 9);
        //
        //        $this->assertEquals(-1, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 4);
        //
        //        $this->assertEquals(-6, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 2.5);
        //
        //        $this->assertEquals(-7, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, 0);
        //
        //        $this->assertEquals(-10, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);
        //
        //        $rank = $this->rank($top, $below, $month, -1);
        //
        //        $this->assertEquals(-11, $rank->value);
        //        $this->assertEquals(-1, $rank->direction);

        $rank = $this->rank(41.28, 35.38, 38.33, 39.35);

        $this->assertEquals(3, $rank->value);
        $this->assertEquals(1, $rank->direction);
    }

    /**
     *
     * 20 = 10
     * 19 = 9
     * 18 = 8
     * 17 = 7
     * 16 = 6
     * 15 = 5
     * 14 = 4
     * 13 = 3
     * 12 = 2
     * 11 = 1
     *
     * 10 = 0
     *
     * 9  = -1
     * 8  = -2
     * 7  = -3
     * 6  = -4
     * 5  = -5
     * 4  = -6
     * 3  = -7
     * 2  = -8
     * 1  = -9
     * 0  = -10
     *
     * @param $top
     * @param $below
     * @param $month
     * @param $price
     *
     * @return object
     */
    private function rank($top, $below, $month, $price): object
    {
        $rank = new \stdClass();

        if ($month == $price) {
            $rank->value = 0;
            $rank->direction = 0;
        }

        if ($month < $price) {
            $rank->direction = 1;
            $diff = $top - $price;
            $width = $top - $month;
        } else {
            $rank->direction = -1;
            $diff = $price - $below;
            $width = $month - $below;
        }

        $rank->value = floor(($width - $diff) / ($width / 10)) * $rank->direction;
        $rank->topDiff = round((($top / $price) - 1) * 100, 1);
        $rank->below = round((($price / $below) - 1) * 100, 1);

        return $rank;
    }
}

