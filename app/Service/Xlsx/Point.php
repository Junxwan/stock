<?php


namespace App\Service\Xlsx;

class Point extends Xlsx
{
    /**
     * 所有分點
     */
    const ALL = 'all';

    /**
     * 外資
     */
    const FOREIGN = 'foreign';

    /**
     * 官股
     */
    const OFFICIAL = 'official';

    /**
     * 經記部
     */
    const JINGJI = 'jingji';

    /**
     * 未列
     */
    const OTHER = 'other';

    /**
     * @var array
     */
    protected $removeIndexs = [0];

    /**
     * @return string
     */
    protected function getDataPath(): string
    {
        return $this->path . '\\' . $this->name();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'point.xlsx';
    }

    /**
     * Sheet index
     *
     * @return mixed
     */
    protected function index()
    {
        return [
            0 => self::ALL,
            1 => self::FOREIGN,
            2 => self::OFFICIAL,
            3 => self::JINGJI,
            4 => self::OTHER,
        ];
    }
}
