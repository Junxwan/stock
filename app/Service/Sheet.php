<?php

namespace App\Service;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface Sheet
{
    /**
     * @param Worksheet $sheet
     * @param int $index
     *
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function putOther(Worksheet $sheet, int $index);

    /**
     * sheet index
     *
     * @return int
     */
    public function index(): int;

    /**
     * 分析主資料
     *
     * @return Collection
     */
    public function getData(): Collection;

    /**
     * 檢查資料
     *
     * @param array $value
     *
     * @return bool
     */
    public function check(array $value): bool;

    /**
     * 種類
     *
     * @return string
     */
    public function type(): string;

    /**
     * 處理到多少筆顯示log
     *
     * @return int
     */
    public function outNum(): int;
}
