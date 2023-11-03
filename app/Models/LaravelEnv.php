<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaravelEnv extends Model
{
    use HasFactory;
	
    protected $table = 'laravel_env';
	protected $primaryKey = 'name';
	protected $casts = [
		'name' => 'string',
	];
	protected $fillable = [
        'name',
        'value',
    ];
	
	public $timestamps = false;
}
