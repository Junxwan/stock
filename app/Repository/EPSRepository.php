<?php

namespace App\Repository;

class EPSRepository extends Repository
{
    /**
     * @param string $code
     * @param $year
     *
     * @return \Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(string $code, string $year)
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('year', $year)
            ->first();
    }

    /**
     * @param array $values
     *
     * @return int
     */
    public function update(array $values)
    {
        return $this->model->newQuery()
            ->where('code', $values['code'])
            ->where('year', $values['year'])
            ->update($values);
    }
}
