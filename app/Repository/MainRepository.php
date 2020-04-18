<?php

namespace App\Repository;

class MainRepository extends Repository
{
    /**
     * @param string $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function codes(string $date)
    {
        return $this->model->newQuery()
            ->where("date", $date)
            ->distinct()
            ->select('code')
            ->get()
            ->pluck('code');
    }
}
