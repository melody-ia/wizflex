<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Nft;

use App\Models\PayToken;

use App\Models\NftLikeCun;

use App\Models\Profile;

use App\Models\CacheNft;

use App\Models\LogTxVerify;

use App\Models\CategoryEnv;

use BlockSDK;
use IPFS;
class TokenController extends Controller
{
	public function netClient($net){
		if($net == 'ETH')
			return BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
		if($net == 'BSC')
			return BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
		if($net == 'KLAY')
			return BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
		if($net == 'MATIC')
			return BlockSDK::createPolygon(envDB('BLOCKSDK_TOKEN'));
	}

	public function getBalance(Request $request,$contractAddress){
		$ownerAddress = $request->ownerAddress;
		
		$payToken = PayToken::where('tokenAddress',$contractAddress)->first();
		if(envDB('HAS_MULTI_NET') == 1 && empty($payToken) == false){
			$net = $payToken->net;
		}else{
			$net = envDB('BASE_MAINNET');
		}
		
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => $contractAddress,
			'method' => 'balanceOf',
			'return_type' => 'bool',
			'parameter_type' => ['address'],
			'parameter_data' => [$ownerAddress]
		]);
		if(empty($data['payload']) == true){
			return response()->json([
				'error' => [
					'message' => "데이터가 없습니다."
				]
			]);
		}
		
		$data = $data['payload'];
		$hex = $this->bchexdec(substr($data['hex'],2));
		return response()->json([
			'data' => $hex
		]);
	}
	
	public function getAllowance(Request $request,$contractAddress){
		$ownerAddress = $request->ownerAddress;
		
		$payToken = PayToken::where('tokenAddress',$contractAddress)->first();
		if(envDB('HAS_MULTI_NET') == 1 && empty($payToken) == false){
			$net = $payToken->net;
		}else{
			$net = envDB('BASE_MAINNET');
		}
		
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => $contractAddress,
			'method' => 'allowance',
			'return_type' => 'bool',
			'parameter_type' => ['address','address'],
			'parameter_data' => [$ownerAddress,$request->senderAddress]
		]);
		if(empty($data['payload']) == true){
			return response()->json([
				'error' => [
					'message' => "데이터가 없습니다."
				]
			]);
		}
		$data = $data['payload'];

		$hex = $this->bchexdec(substr($data['hex'],2));
		//$this->cacheSave('offer_' . $token_id,$hex);

		//return $hex;
		return response()->json([
			'data' => $hex
		]);
	}
	
    public function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, $this->bchexdec($remain)), hexdec($last));
        }
    }

}