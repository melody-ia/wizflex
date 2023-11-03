<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchase';

	protected $fillable = [
        'nft_id',
        'tx_hash',
    ];


	public function transfer(){
		return Transfer::find($this->tx_hash);
	}

//    public function testing(){
//        return $this->hasMany(Transfer::class, 'tx_hash');
//    }

}
