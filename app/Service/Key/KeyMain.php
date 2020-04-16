<?php

namespace App\Service\Key;

use App\Service\Data;
use App\Service\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class KeyMain implements Sheet
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var array
     */
    protected $codes = [];

    /**
     * All constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return $this->data->getMainKey();
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    public function check(array $value): bool
    {
        return ! empty($value[$this->type()]);
    }

    /**
     * @param Worksheet $sheet
     * @param int $index
     *
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function putOther(Worksheet $sheet, int $index)
    {
        $sheet->getCell('A' . ($index + 2))->setValue(implode(";", $this->codes));
    }

    /**
     * @return int
     */
    public function outNum(): int
    {
        return 10;
    }

    /**
     * @param array $value
     *
     * @return array
     */
    public function putData(array $value): array
    {
        $this->codes[] = $value["code"];

        return [
            'A' => $value["code"],
            'B' => $value["name"],
            'C' => implode(',', $value[$this->type()]),
        ];
    }

    /**
     * 資料欄位
     *
     * @return Collection
     */
    public function getColumns(): Collection
    {
        return collect([
            // 個股行情
            'info' => [
                'G' => 'month_Slope',
                'H' => 'bb_top_Slope',
                'I' => 'bb_below_Slope',
                'F' => 'bandwidth',
                'E' => 'rank',
                'D' => 'increase',
                'J' => 'top_diff_lie',
                'K' => 'below_diff_lie',
                'L' => 'main_1',
                'M' => 'main_5',
                'N' => 'main_10',
                'O' => 'main_20',
                'P' => 'year_stray',
                'Q' => 'season_stray',
                'R' => 'month_stray',
                'S' => 'high_stray',
                'T' => 'low_stray',
                'U' => 'main_buy_n',
                'V' => 'foreign_investment_buy_n',
                'W' => 'trust_buy_n',
                'X' => 'self_employed_buy_n',
                'Y' => 'foreign_investment_ratio',
                'Z' => 'credit_ratio',
                'AA' => 'self_employed_ratio',
                'AB' => 'yoy',
                'AC' => 'mom',
                'AD' => 'financing_maintenance',
                'AE' => 'financing_use',
                'AF' => 'net_worth',
                'AG' => 'securities_ratio',
                'AH' => 'compulsory_replenishment_day',
                'AI' => 'volume_20_multiple',
            ],

            // eps欄位
            'eps' => [
                'AJ' => '',
                'AK' => '',
                'AL' => '',
                'AM' => '',
                'AN' => '',
                'AO' => '',
                'AP' => '',
            ],

            // 關鍵分點買賣欄位
            'main' => 'C',

            // 離高
            'high_stray' => 'S',

            // 融資維持
            'financing_maintenance' => 'AD',

            // 融資使用
            'financing_use' => 'AE',

            // 股價淨值比
            'net_worth' => 'AF',

            // 帶寬
            'bandwidth' => 'F',

            // 資卷比
            'securities_ratio' => 'G',

            // 20日成交均量幾倍
            'volume_20_multiple' => 'AI',

            // 斜率
            'slope' => [
                'G',
                'H',
                'I',
            ],

            // 日期
            'date' => [
                'AH',
                'AP',
            ],

            // 漲幅 位階 主1 主5 主10 主20 年乖 月乖 週乖 離低 主力N 外資N 投信N 自營商N yoy mom 離上通% 離下通% 外資% 投信% 自營商%
            'style' => [
                'D',
                'E',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                'R',
                'T',
                'U',
                'V',
                'W',
                'X',
                'AB',
                'AC',
                'J',
                'K',
                'Y',
                'Z',
                'AA',
            ],
        ]);
    }
}
