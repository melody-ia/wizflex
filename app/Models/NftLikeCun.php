<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NftLikeCun extends Model
{
    use HasFactory;
	
    protected $table = 'nft_like_cun';
	protected $primaryKey = 'token_id';

	protected $fillable = [
        'token_id',
        'cun'
    ];
}
