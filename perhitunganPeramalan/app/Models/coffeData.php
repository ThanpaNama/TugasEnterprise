<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class coffeData extends Model
{
    protected $table = 'coffe_data';
    protected $fillable = [
        'date',
        'datetime',
        'cash_type',
        'card',
        'moneyy',
        'coffe_name',
    ];
}
