<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogContractCallback extends Model
{
    use HasFactory;
	
	
    protected $table = 'log_contract_callback';
	protected $primaryKey = 'tx_hash';
	public $incrementing = false;
	
	protected $fillable = [
        'tx_hash'
    ];
}