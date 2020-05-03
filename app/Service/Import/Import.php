<?php

/**
 * 匯入來自xlsx資料
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\OpenDateRepository;
use App\Repository\PointRepository;
use App\Repository\PriceRepository;
use App\Repository\StockRepository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class Import
{
    use InteractsWithIO;

    /**
     * @var Xlsx
     */
    protected $xlsx;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string|int
     */
    protected $year;

    /**
     * @var StockRepository
     */
    protected $stockRepo;

    /**
     * @var PointRepository
     */
    protected $pointRepo;

    /**
     * @var OpenDateRepository
     */
    protected $openDateRepo;

    /**
     * @var PriceRepository
     */
    protected $priceRepo;

    /**
     * import constructor.
     *
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Xlsx $xlsx)
    {
        $this->xlsx = $xlsx;
        $this->date = $xlsx->date();
        $this->year = $xlsx->year();

        $this->stockRepo = app(StockRepository::class);
        $this->pointRepo = app(PointRepository::class);
        $this->openDateRepo = app(OpenDateRepository::class);
        $this->priceRepo = app(PriceRepository::class);

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * 新增
     *
     * @param Collection $data
     *
     * @return bool
     */
    abstract protected function insert(Collection $data): bool;

    /**
     * 寫檔
     *
     * @return bool
     */
    public function write(): bool
    {
        try {
            $data = $this->xlsx->getData();
            $this->info("save " . $this->xlsx->name() . ' ....');

            $result = $this->insert($data);

            if (! $result) {
                $this->error("save " . $this->xlsx->name() . ' failure');
            }

            return $result;
        } catch (StockException $e) {
            Log::error('code: ' . $e->getCode() . ' error: ' . $e->getMessage() . ' trace: ' . $e->getTraceAsString());
        } catch (\Exception $e) {
            Log::error(' error: ' . $e->getMessage() . ' trace: ' . $e->getTraceAsString());
        }

        return false;
    }

    /**
     * 所有股票代碼
     *
     * @return array
     */
    protected function allCodes(): array
    {
        return $this->stockRepo->all()->pluck('code')->all();
    }

    /**
     * 所有股票代碼
     *
     * @param Collection $data
     *
     * @return Collection
     */
    protected function getCodes(Collection $data)
    {
        $codes = array_filter($data->pluck('0')->toArray());
        $unique = array_unique($codes);

        return collect([
            'repeat' => array_diff_assoc($codes, $unique),
            'code' => $unique,
        ]);
    }

    /**
     * 撿查股票代碼
     *
     * @param Collection $data
     *
     * @return bool
     */
    protected function checkCode(Collection $data)
    {
        $codes = $this->getCodes($data);
        if ($this->checkRepeat($codes) || $this->checkDiff($codes)) {
            return true;
        }

        return false;
    }

    /**
     * 撿查是否有重覆股票
     *
     * @param Collection $data
     *
     * @return bool
     */
    protected function checkRepeat(Collection $data): bool
    {
        if (count($data['repeat']) > 0) {
            $this->error('==================================================');
            $this->error('repeat code: ' . implode(',', $data['repeat']));
            return true;
        }

        return false;
    }

    /**
     * 撿查是否有股票是否存在資料庫清單中
     *
     * @param Collection $codes
     *
     * @return bool
     */
    protected function checkDiff(Collection $data): bool
    {
        $diff = array_diff($data['code'], $this->allCodes());
        if (count($diff) > 0) {
            $this->error('==================================================');
            $this->error('diff code: ' . implode(',', $diff));
            return true;
        }

        return false;
    }

    /**
     * 四捨五入
     *
     * @param $value
     * @param int $precision
     *
     * @return false|float
     */
    protected function round($value, int $precision = 0)
    {
        return round($this->format($value), $precision);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function formatDate($value)
    {
        if ($value == '') {
            return null;
        }

        return Carbon::createFromFormat('Ymd', $value)->format('Y-m-d');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function format($value)
    {
        return $value == "" ? 0 : $value;
    }

    /**
     * @param string $message
     */
    protected function log(string $message)
    {
        Log::info($message);
        $this->info($message);
    }
}
