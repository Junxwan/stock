<?php

/**
 * 匯入來自xlsx資料
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\Repository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
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
    private $xlsx;

    /**
     * @var string
     */
    protected $date;

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
}
