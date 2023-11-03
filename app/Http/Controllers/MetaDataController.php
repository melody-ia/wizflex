<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use BlockSDK;

use App\Models\Nft;
use App\Models\CacheNft;
class MetaDataController extends Controller
{
	public function netClient(){
		if(envDB('BASE_MAINNET') == 'ETH')
			return BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'BSC')
			return BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'KLAY')
			return BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
	}
	
	public function cacheSave($id,$hex){
		$cacheNft = CacheNft::find($id);
		if(empty($cacheNft) == true){
			$cacheNft = new CacheNft();
		}
		
		$cacheNft->id = $id;
		$cacheNft->data = $hex;
		$cacheNft->save();
	}
	
	public function netTokenInfo($token_id){
		return $this->netClient()->getNftInfo([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'token_id' => $token_id,
		]);
	}
	public function getTokenInfo($token_id){
		
		$data = $this->netClient()->getKIP17TokenInfo([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'token_id' => $token_id,
		]);
		
		$json = json_encode($data['payload']);
		$this->cacheSave('tokeninfo_' . $token_id,$json);
		
		return $data['payload'];
	}	
	
	public function get(Request $request,$nft_id){
		$nft = Nft::find($nft_id);
		if(empty($nft) == true){
			return response()->json([
				'error' => [
					'message' => '등록되지 않은 NFT'
				]
			]);
		}
		
		$tokenInfo = $this->getTokenInfo($nft->token_id);

		$result = [
			'id' => $nft->id,
			'category' => $nft->category,
			'token_id' => $token_id,
			'name' => $nft->name,
			'description' => $nft->description,
			'creator' => $nft->creator_address,
			'owner' => $tokenInfo['owner'],
			
			'metadata_uri'   => "ipfs://" . $nft->id,
			'metadata_gateway_url' => envDB("BASE_IPFS_GATEWAY") . '/' .$nft->id,
			
			'image_uri'   => 'ipfs://' . '/' .$nft->ipfs_image_hash,
			'image_gateway_url'   => envDB("BASE_IPFS_GATEWAY") . '/' .$nft->id,
		];
		
		if(substr($nft->file_name,-4) == '.mp4'){
			$result['video'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
		}else{
			$result['image'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
		}
		
		return response()->json($result);
	}
}