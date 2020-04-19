<?php

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\EPSRepository;
use App\Service\Xlsx\Xlsx;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EPS extends Import
{
    /**
     * @var EPSRepository
     */
    private $repo;

    /**
     * EPS constructor.
     *
     * @param EPSRepository $repo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(EPSRepository $repo, Xlsx $xlsx)
    {
        $this->repo = $repo;
        parent::__construct($xlsx);
    }

    /**
     *
     * 0 代碼
     * 1 名稱
     * 2 q4 eps
     * 3 q3 eps
     * 4 q2 eps
     * 5 q1 eps
     * 6 q1-q3 eps
     * 7 eps
     * 8 股票股利發放率 = (盈餘配股 + 公積配股) / 年度eps
     * 9 現金股利發放率 = 現金股利 / 年度eps
     * 10 股利發放率 = 股利合計 / 年度eps
     * 11 連續N年發放現金股利
     * 12 除權日
     * 13 除息日
     * 14 董事會決議股利派發日
     * 15 股利政策-盈餘配股
     * 16 股利政策-公積配股
     * 17 股利政策-股票股利合計 = 盈餘配股 + 公積配股
     * 18 股利政策-盈餘配息
     * 19 股利政策-公積配息
     * 20 股利政策-現金股利 = 盈餘配息 + 公積配息
     * 21 股利政策-年度現金股利 = 盈餘配息 + 公積配息
     * 22 股利政策-年度股利合計 = 股票股利合計 + 年度現金股利
     *
     * @param Collection $data
     *
     * @return bool
     */
    protected function insert(Collection $data): bool
    {
        $code = 0;
        $saveTotal = 0;
        $updateTotal = 0;
        $updateCodes = [];

        if ($this->checkCode($data)) {
            return true;
        }

        try {
            foreach ($data->all() as $i => $value) {
                if ($value[1] == '') {
                    continue;
                }

                $code = $value[0];

                $exDividendDay = $value[12] == "" ? $value[13] : $value[12];
                if ($exDividendDay != '') {
                    $exDividendDay = Carbon::createFromFormat('Ymd', $exDividendDay)->format('Y-m-d');
                }

                $resolutionMeeting = $value[14];
                if ($resolutionMeeting != '') {
                    $resolutionMeeting = Carbon::createFromFormat('Ymd', $resolutionMeeting)->format('Y-m-d');
                }

                $insert = [
                    'code' => $code,
                    'year' => $this->year,
                    'q4' => $this->formatInt($value[2]),
                    'q3' => $this->formatInt($value[3]),
                    'q2' => $this->formatInt($value[4]),
                    'q1' => $this->formatInt($value[5]),
                    'q1_q3' => $this->formatInt($value[6]),
                    'eps' => $this->formatInt($value[7]),

                    'dividend_stock_surplus' => $this->formatInt($value[15]),
                    'dividend_stock_provident' => $this->formatInt($value[16]),
                    'dividend_stock_total' => $this->formatInt($value[17]),

                    'dividend_cash_surplus' => $this->formatInt($value[18]),
                    'dividend_cash_provident' => $this->formatInt($value[19]),
                    'dividend_cash_cash' => $this->formatInt($value[20]),
                    'dividend_cash_total' => $this->formatInt($value[21]),

                    'dividend_total' => $this->formatInt($value[22]),

                    'distribution_rate_stock' => $this->formatInt($value[8]),
                    'distribution_rate_cash' => $this->formatInt($value[9]),
                    'distribution_rate_total' => $this->formatInt($value[10]),

                    'n_rate' => $this->formatInt($value[11]),
                    'ex_dividend_day' => $exDividendDay ?: null,
                    'resolution_meeting' => $resolutionMeeting ?: null,
                ];

                $result = false;
                $action = false;
                $model = $this->repo->get($code, $this->date);

                if ($model == null) {
                    $action = true;
                    $saveTotal++;
                    $result = $this->repo->insert($insert);
                } else {
                    foreach ($model->toArray() as $k => $v) {
                        if ($insert[$k] != $v) {
                            $result = $this->repo->update($insert);
                            $action = true;
                            $updateTotal++;
                            $updateCodes[] = $code;
                            break;
                        }
                    }
                }

                if ($result) {
                    $this->info('code: ' . $code);
                } elseif ($action) {
                    $this->error('error code: ' . $code);
                    return false;
                }
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        $codes = $this->getCodes($data);

        $this->info('total: ' . count($codes['code']) . ' save: ' . $saveTotal . ' update: ' . $updateTotal);

        if (count($updateCodes) > 0) {
            $this->info('==============================================');
            $this->info('update code: ' . implode(',', $updateCodes));
        }

        return true;
    }
}
