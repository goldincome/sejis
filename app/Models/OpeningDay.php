<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningDay extends Model
{
     protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'status',
        'total_mins'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];
}
