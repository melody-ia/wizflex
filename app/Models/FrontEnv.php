<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontEnv extends Model
{
    use HasFactory;
	
    protected $table = 'front_env';
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
