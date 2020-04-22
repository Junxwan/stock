<?php

namespace App\Service\Export;

use App\Repository\KeyResultRepository;
use App\Repository\PriceRepository;
use App\Repository\PriceResultRepository;
use Illuminate\Support\Collection;

class Data
{
    /**
     * @var PriceRepository
     */
    protected $priceRepo;

    /**
     * @var PriceResultRepository
     */
    protected $priceResultRepo;

    /**
     * @var KeyResultRepository
     */
    protected $keyResultRepo;

    /**
     * @var Collection
     */
    private $price;

    /**
     * @var Collection
     */
    private $result;

    /**
     * @var Collection
     */
    private $main;

    /**
     * Write constructor.
     *
     * @param PriceRepository $priceRepo
     * @param PriceResultRepository $priceResultRepo
     * @param KeyResultRepository $keyResultRepo
     */
    public function __construct(
        PriceRepository $priceRepo,
        PriceResultRepository $priceResultRepo,
        KeyResultRepository $keyResultRepo
    ) {
        $this->priceRepo = $priceRepo;
        $this->priceResultRepo = $priceResultRepo;
        $this->keyResultRepo = $keyResultRepo;

    }

    /**
     * 初始化個股行情資料
     *
     * @param string $date
     */
    public function init(string $date)
    {
        $this->price = $this->doKey($this->priceRepo->date($date));
        $this->result = $this->doKey($this->priceResultRepo->date($date));
        $this->main = $this->keyResultRepo->date($date)->groupBy('code');
    }

    /**
     * 個股行情資料
     *
     * @return Collection
     */
    public function price()
    {
        return collect([
            'price' => $this->price,
            'result' => $this->result,
            'main' => $this->main,
        ]);
    }

    /**
     * @param Collection $data
     *
     * @return Collection
     */
    private function doKey(Collection $data): Collection
    {
        $key = collect();
        foreach ($data as $value) {
            $key->put($value->code, $value);
        }

        return $key;
    }
}
