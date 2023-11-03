<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryEnv extends Model
{
    use HasFactory;
	
    protected $table = 'category_env';
	protected $primaryKey = 'name';
	protected $casts = [
		'name' => 'string',
	];
	protected $fillable = [
        'name',
        'value',
        'footer',
    ];
	
	public $timestamps = false;
}