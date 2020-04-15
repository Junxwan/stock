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
     * @return Collection
     */
    public function getData(): Collection
    {
        return $this->highEndShipping;
    }
}
