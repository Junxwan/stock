<?php

namespace App\Service\Key;

use App\Service\Data;
use App\Service\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class KeyMain implements Sheet, Column
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
     * @param Collection $price
     * @param Collection $eps
     * @param array $value
     *
     * @return bool
     */
    public function check(
        Collection $price,
        Collection $eps,
        array $value
    ): bool {
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
     * @param Collection $price
     * @param array $value
     *
     * @return array
     */
    public function data(Collection $price, array $value): array
    {
        $this->codes[] = $price["code"];

        return [
            'A' => $price["code"],
            'B' => $price["name"],
            'C' => implode(',', $value[$this->type()]),
        ];
    }

    /**
     * @return array
     */
    public function info(): array
    {
        return [
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
            'Y' => 'yoy',
            'Z' => 'mom',
            'AA' => 'financing_maintenance',
            'AB' => 'financing_use',
            'AC' => 'net_worth',
            'AD' => 'securities_ratio',
            'AE' => 'compulsory_replenishment_day',
            'AF' => 'volume_20_multiple',
        ];
    }

    /**
     * @return array
     */
    public function eps(): array
    {
        return [
            'AG' => '',
            'AH' => '',
            'AI' => '',
            'AJ' => '',
            'AK' => '',
            'AL' => '',
            'AM' => '',
        ];
    }

    /**
     * @return array
     */
    public function gets(): array
    {
        // 漲幅 位階 主1 主5 主10 主20 年乖 月乖 週乖 離低 主力N 外資N 投信N 自營商N yoy mom 離上通% 離下通%
        return ['D', 'E', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'J', 'K'];
    }

    /**
     * @return string
     */
    public function mainKey(): string
    {
        return 'C';
    }

    /**
     * @return string
     */
    public function highStray(): string
    {
        return 'S';
    }

    /**
     * @return string
     */
    public function financingMaintenance(): string
    {
        return 'AA';
    }

    /**
     * @return string
     */
    public function financingUse(): string
    {
        return 'AB';
    }

    /**
     * @return string
     */
    public function netWorth(): string
    {
        return 'AC';
    }

    /**
     * @return string
     */
    public function bandwidth(): string
    {
        return 'F';
    }

    /**
     * @return array
     */
    public function slope(): array
    {
        return ['G', 'H', 'I'];
    }

    /**
     *
     * @return string
     */
    public function securitiesRatio(): string
    {
        return 'AD';
    }

    /**
     * @return array
     */
    public function date(): array
    {
        return ['AE', 'AM'];
    }

    /**
     *
     * @return string
     */
    public function volume20Multiple(): string
    {
        return 'AF';
    }
}
