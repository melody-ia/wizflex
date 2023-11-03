<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayToken extends Model
{
    use HasFactory;
	
    protected $table = 'pay_tokens';


	protected $fillable = [
        'name',
        'symbol',
        'decimals',
		'img_url',
		'contract'
    ];
}
