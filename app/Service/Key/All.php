<?php

namespace App\Service\Key;

use App\Service\Data;
use App\Service\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class All implements Sheet, Column
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
     * @param Collection $price
     * @param array $value
     *
     * @return array
     */
    public function data(Collection $price, array $value): array
    {
        return [
            'A' => $price["code"],
            'B' => $price["name"],
        ];
    }

    /**
     * @return array
     */
    public function info(): array
    {
        return [
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
            'X' => 'yoy',
            'Y' => 'mom',
            'Z' => 'financing_maintenance',
            'AA' => 'financing_use',
            'AB' => 'net_worth',
            'AC' => 'securities_ratio',
            'AE' => 'volume_20_multiple',
            'AD' => 'compulsory_replenishment_day',
        ];
    }

    /**
     * @return array
     */
    public function eps(): array
    {
        return [
            'AF' => '',
            'AG' => '',
            'AH' => '',
            'AI' => '',
            'AJ' => '',
            'AK' => '',
            'AL' => '',
        ];
    }

    /**
     * @return array
     */
    public function gets(): array
    {
        // 漲幅 位階 主1 主5 主10 主20 年乖 月乖 週乖 離低 yoy mom 離上通% 離下通% 主力連N買 外資連N買 投信連N買 自營商連N買
        return [
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
            'X',
            'Y',
            'U',
            'I',
            'J',
            'T',
            'U',
            'V',
            'W',
        ];
    }

    /**
     * @return string
     */
    public function mainKey(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function highStray(): string
    {
        return 'R';
    }

    /**
     * @return string
     */
    public function financingMaintenance(): string
    {
        return 'Z';
    }

    /**
     * @return string
     */
    public function financingUse(): string
    {
        return 'AA';
    }

    /**
     * @return string
     */
    public function netWorth(): string
    {
        return 'AB';
    }

    /**
     * @return string
     */
    public function bandwidth(): string
    {
        return 'E';
    }

    /**
     * @return array
     */
    public function slope(): array
    {
        return [
            'F',
            'G',
            'H',
        ];
    }

    /**
     * @return string
     */
    public function securitiesRatio(): string
    {
        return 'AC';
    }

    /**
     * @return array
     */
    public function date(): array
    {
        return ['AD', 'AL'];
    }

    /**
     * @return string
     */
    public function volume20Multiple(): string
    {
        return 'AE';
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
}
