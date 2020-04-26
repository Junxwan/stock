<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $casts = [
        'date' => 'date',
    ];

    public $timestamps = false;
}
