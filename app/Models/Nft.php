<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use DateTimeInterface;

class Nft extends Model
{
    use HasFactory;

    protected $table = 'nft';
	protected $casts = [
		'id' => 'string',
	];
	protected $fillable = [
        'id',
        'ipfs_image_hash',
        'creator_address',
        'category',
		'interface',
        'name',
		'description',
		'file_name',
		'tx_hash',
		'token_id',
		'token_amount',
		'year_creation',
		'net',
		'contract_address',
		'creator_ip',
    ];

	public function file(){
//        return 'test';
		if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $this->file_name)) == false){
			return envDB('BASE_AWS_S3_URI') . '/nft-files/' . $this->file_name;
		}

		return envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $this->file_name);
	}

	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
