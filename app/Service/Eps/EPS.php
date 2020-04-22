<?php

namespace App\Service\Eps;

use App\Service\Sheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EPS implements Sheet
{
    /**
     * @var Data
     */
    private $data;

    /**
     * EPS constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
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
    }

    /**
     * @return int
     */
    public function index(): int
    {
        return 0;
    }

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return $this->data->getEps();
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
     * @inheritDoc
     */
    public function type(): string
    {
        return '';
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
     * @return Collection
     */
    public function getColumns(): Collection
    {
        return collect([
            'info' => [
                'C' => 'eps.q1_q3',
            ],
        ]);
    }
}
