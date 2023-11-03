<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaySale extends Model
{
    use HasFactory;
	
    protected $table = 'day_sales';
	
	protected $fillable = [
        'blockchain',
        'address',
        'amount',
        'date',
    ];
}