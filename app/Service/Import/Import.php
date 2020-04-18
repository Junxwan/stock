<?php

/**
 * 匯入來自xlsx資料
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\StockRepository;
use App\Service\Arr;
use App\Service\Xlsx\Xlsx;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Model\Stock as Model;

abstract class Import
{
    use InteractsWithIO;

    /**
     * @var Xlsx
     */
    private $xlsx;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var StockRepository
     */
    protected $stockRepo;

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
        $this->stockRepo = app(StockRepository::class, [
            'model' => app(Model::class),
        ]);

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
        if ($this->checkRepeat($codes)) {
            return true;
        }

        if ($this->checkDiff($codes)) {
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
        if ($data->where('repeat')->isNotEmpty()) {
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
        $diff = array_diff($data->get('code'), $this->allCodes());
        if (count($diff) > 0) {
            $this->error('==================================================');
            $this->error('diff code: ' . implode(',', $diff['repeat']));
            return true;
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return int
     */
    protected function formatInt($value)
    {
        return $value == "" ? 0 : $value;
    }
}
