<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;
	
    protected $table = 'transfer';
	protected $primaryKey = 'tx_hash';
	protected $casts = [
		'tx_hash' => 'string',
	];
	
	protected $fillable = [
        'tx_hash',
        'from',
        'to',
        'value',
        'peace'
    ];
}
