<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Main extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'date',
        'name',
        'count',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
