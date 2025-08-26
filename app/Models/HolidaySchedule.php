<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidaySchedule extends Model
{
    protected $fillable = [
        'title',
        'date_from',
        'date_to',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_from' => 'datetime',
        'date_to' => 'datetime',
    ];
}
