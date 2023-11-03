<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionLike extends Model
{
    use HasFactory;
	
    protected $table = 'collection_like';


	protected $fillable = [
        'id',
        'address',
        'to_address'
    ];
}
