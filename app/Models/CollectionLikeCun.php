<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionLikeCun extends Model
{
    use HasFactory;
	
    protected $table = 'collection_like_cun';
	protected $primaryKey = 'address';


	protected $fillable = [
        'address',
        'cun'
    ];
}
