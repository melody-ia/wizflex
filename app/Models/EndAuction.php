<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndAuction extends Model
{
    use HasFactory;
	
    protected $table = 'end_auction';

	protected $fillable = [
        'nft_id',
        'tx_hash',
    ];
	
	
	public function transfer(){
		return Transfer::find($this->tx_hash);
	}
}
