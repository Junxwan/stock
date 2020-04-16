<?php

namespace App\Service\Key;

use App\Service\Data;
use App\Service\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class All implements Sheet
{
    /**
     * @var Data
     */
    private $data;

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
     * @return int
     */
    public function index(): int
    {
        return 2;
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    public function check(array $value): bool
    {
        return true;
    }

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return $this->data->getMain();
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return "all";
    }

    /**
     * @return int
     */
    public function outNum(): int
    {
        return 50;
    }

    /**
     * @param array $value
     *
     * @return array
     */
    public function putData(array $value): array
    {
        return [
            'A' => $value["code"],
            'B' => $value["name"],
        ];
    }

    /**
     * @param Worksheet $sheet
     * @param int $index
     *
     * @return mixed|void
     */
    public function putOther(Worksheet $sheet, int $index)
    {
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
                'F' => 'month_Slope',
                'G' => 'bb_top_Slope',
                'H' => 'bb_below_Slope',
                'E' => 'bandwidth',
                'D' => 'rank',
                'C' => 'increase',
                'I' => 'top_diff_lie',
                'J' => 'below_diff_lie',
                'K' => 'main_1',
                'L' => 'main_5',
                'M' => 'main_10',
                'N' => 'main_20',
                'O' => 'year_stray',
                'P' => 'season_stray',
                'Q' => 'month_stray',
                'R' => 'high_stray',
                'S' => 'low_stray',
                'T' => 'main_buy_n',
                'U' => 'foreign_investment_buy_n',
                'V' => 'trust_buy_n',
                'W' => 'self_employed_buy_n',
                'X' => 'foreign_investment_ratio',
                'Y' => 'credit_ratio',
                'Z' => 'self_employed_ratio',
                'AA' => 'yoy',
                'AB' => 'mom',
                'AC' => 'financing_maintenance',
                'AD' => 'financing_use',
                'AE' => 'net_worth',
                'AF' => 'securities_ratio',
                'AH' => 'volume_20_multiple',
                'AG' => 'compulsory_replenishment_day',
            ],

            // eps欄位
            'eps' => [
                'AI' => '',
                'AJ' => '',
                'AK' => '',
                'AL' => '',
                'AM' => '',
                'AN' => '',
                'AO' => '',
            ],

            // 關鍵分點買賣欄位
            'main' => '',

            // 離高
            'high_stray' => 'R',

            // 融資維持
            'financing_maintenance' => 'AC',

            // 融資使用
            'financing_use' => 'AD',

            // 股價淨值比
            'net_worth' => 'AE',

            // 帶寬
            'bandwidth' => 'E',

            // 資卷比
            'securities_ratio' => 'AF',

            // 20日成交均量幾倍
            'volume_20_multiple' => 'AH',

            // 斜率
            'slope' => [
                'F',
                'G',
                'H',
            ],

            // 日期
            'date' => [
                'AG',
                'AO',
            ],

            // 漲幅 位階 主1 主5 主10 主20 年乖 月乖 週乖 離低 離上通% 離下通% 主力連N買 外資連N買 投信連N買 自營商連N買 yoy mom 外資%
            // 投信% 自營商%
            'style' => [
                'C',
                'D',
                'K',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                "S",
                'T',
                'U',
                'I',
                'J',
                'T',
                'U',
                'V',
                'W',
                'AA',
                'AB',
                'X',
                'Y',
                'Z',
            ],
        ]);
    }
}
