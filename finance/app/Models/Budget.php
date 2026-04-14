<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'planned_amount',
        'spent_amount',
        'period_start',
        'period_end',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}
