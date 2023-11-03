<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayerValue extends Model
{
    use HasFactory;
	
    protected $table = 'layer_value';
	protected $fillable = [
        'value',
        'image',
    ];
	
	public $timestamps = false;
}
