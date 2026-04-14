<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id', 'account_id', 'category_id', 'amount', 
        'operation_date', 'operation_time', 'description', 
        'location', 'status', 'transfer_account_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Связь с категорией
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

}
