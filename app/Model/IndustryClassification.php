<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IndustryClassification extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'tw_name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
