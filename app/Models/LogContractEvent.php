<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogContractEvent extends Model
{
    use HasFactory;
	
	
    protected $table = 'log_contract_event';
	protected $primaryKey = ['tx_hash', 'method'];
	public $incrementing = false;
	
	
	protected $fillable = [
        'tx_hash',
        'method'
    ];
}