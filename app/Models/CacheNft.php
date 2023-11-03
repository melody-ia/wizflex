<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheNft extends Model
{
    use HasFactory;
	
    protected $table = 'cache_nft';
	protected $casts = [
		'id' => 'string',
	];

	protected $fillable = [
        'id',
        'data',
    ];
}
