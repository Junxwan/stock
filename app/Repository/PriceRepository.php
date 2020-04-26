<?php

namespace App\Repository;

use App\Model\Price;

class PriceRepository extends Repository
{
    /**
     * PriceRepository constructor.
     *
     * @param Price $model
     */
    public function __construct(Price $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $code
     * @param string $date
     * @param array $values
     *
     * @return bool
     */
    public function update(string $code, string $date, array $values): bool
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('date', $date)
            ->update($values);
    }

    /**
     * @param string $code
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(string $code, string $date)
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('date', $date)
            ->first();
    }

    /**
     * @param string $year
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function year(string $year)
    {
        return $this->model->newQuery()
            ->whereBetween('date', [$year . '-01-01', $year . '-12-31'])
            ->get();
    }

    /**
     * @param string $code
     * @param string $year
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getYear(string $code, string $year)
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->whereBetween('date', [$year . '-01-01', $year . '-12-31'])
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @param string $code
     * @param string $year
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAfterYear(string $code, string $year)
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('date', '<=', $year . '-12-31')
            ->orderByDesc('date')
            ->get();
    }

    /**
     *
     * 批次更新
     *
     * Update prices set open =
     *  CASE
     *      WHEN `date` = '2019-12-31' then 1
     *      WHEN `date` = '2019-12-30' then 2
     *  ELSE 0
     *  END WHERE code = '1101';
     *
     * @param string $code
     * @param string $updateKey
     * @param array $values
     *
     * @return int
     */
    public function batchUpdate(string $code, string $updateKey, array $values)
    {
        $when = [];
        foreach ($values as $value) {
            $when[] = "WHEN `date` = '" . $value['date'] . "' then " . $value[$updateKey];
        }

        $query = "UPDATE `" . $this->model->getTable() .
            "` SET `" . $updateKey . "` = CASE " . implode(' ', $when) . ' ELSE 0 END' .
            " WHERE `code` = " . $code;

        return $this->model->getConnection()->update($query);
    }
}
