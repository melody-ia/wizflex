<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountPurchase extends Model
{
    use HasFactory;
	
	public $timestamps = false;
	
    protected $table = 'count_purchase';
	
	protected $fillable = [
        'total',
        'cun',
        'year',
        'month',
        'day',
        'hour',
        'minute',
    ];
}