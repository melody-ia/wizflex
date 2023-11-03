<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogTxVerify extends Model
{
    use HasFactory;
	
    protected $table = 'log_tx_verify';
	protected $primaryKey = ['tx_hash'];
	public $incrementing = false;
	
	
	protected $fillable = [
        'tx_hash',
    ];
}