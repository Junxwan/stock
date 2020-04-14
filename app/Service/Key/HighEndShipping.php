<?php

namespace App\Service\Key;

use App\Service\Data;
use Illuminate\Support\Collection;

class HighEndShipping extends All
{
    /**
     * @var Collection
     */
    private $highEndShipping;

    /**
     * HighEndShipping constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->highEndShipping = $data->getHighEndShipping();
        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function index(): int
    {
        return 3;
    }

    /**
     * @param Collection $price
     * @param Collection $eps
     * @param array $value
     *
     * @return bool
     */
    public function check(Collection $price, Collection $eps, array $value): bool
    {
        return $this->highEndShipping->where("code", $value["code"])->isNotEmpty();
    }
}
