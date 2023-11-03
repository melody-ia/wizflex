<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayerType extends Model
{
    use HasFactory;
	
    protected $table = 'layer_type';
	protected $primaryKey = 'name';
	protected $casts = [
		'name' => 'string',
	];
	protected $fillable = [
        'priority',
        'name',
    ];
	
	public $timestamps = false;
}
