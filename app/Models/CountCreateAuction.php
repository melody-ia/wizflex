<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountCreateAuction extends Model
{
    use HasFactory;
	
	public $timestamps = false;
	
    protected $table = 'count_create_auction';
	
	protected $fillable = [
        'cun',
        'year',
        'month',
        'day',
        'hour',
        'minute',
    ];
}
