<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NftLike extends Model
{
    use HasFactory;
	
    protected $table = 'nft_like';


	protected $fillable = [
        'id',
        'address',
        'token_id'
    ];
}
