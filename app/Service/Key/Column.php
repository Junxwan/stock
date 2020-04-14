<?php

namespace App\Service\Key;

use Illuminate\Support\Collection;

interface Column
{
    /**
     * 普通欄位
     *
     * @return array
     */
    public function gets(): array;

    /**
     * 欄位資料
     *
     * @param Collection $price
     * @param array $value
     *
     * @return array
     */
    public function data(Collection $price, array $value): array;

    /**
     * 個股行情欄位
     *
     * @return array
     */
    public function infoDataColumn(): array;

    /**
     * eps欄位
     *
     * @return array
     */
    public function epsDataColumn(): array;

    /**
     * 關鍵分點買賣欄位
     *
     * @return string
     */
    public function mainKey(): string;

    /**
     * 離高
     *
     * @return string
     */
    public function highStray(): string;

    /**
     * 融資維持
     *
     * @return string
     */
    public function financingMaintenance(): string;

    /**
     * 融資使用
     *
     * @return string
     */
    public function financingUse(): string;

    /**
     * 股價淨值比
     *
     * @return string
     */
    public function netWorth(): string;

    /**
     * 帶寬
     *
     * @return string
     */
    public function bandwidth(): string;

    /**
     * 斜率
     *
     * @return array
     */
    public function slope(): array;

    /**
     * 資卷比
     *
     * @return string
     */
    public function securitiesRatio(): string;

    /**
     * 20日成交均量幾倍
     *
     * @return string
     */
    public function volume20Multiple(): string;

    /**
     * 日期
     *
     * @return array
     */
    public function date(): array;
}
