<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'capital',
        'industry_code',
        'classification',
        'issued',
        'twse_date',
        'otc_date',
        'creation_date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'twse_date' => 'datetime',
        'otc_date' => 'datetime',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return mixed
     */
    public function pushDate()
    {
        if (isset($this->twse_date)) {
            return $this->twse_date;
        }

        return $this->otc_date;
    }
}
