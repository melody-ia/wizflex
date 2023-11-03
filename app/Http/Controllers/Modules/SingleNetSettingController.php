<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\User;

use App\Models\LaravelEnv;
use App\Models\FrontEnv;
use App\Models\CategoryEnv;

use BlockSDK;
trait SingleNetSettingController {
	public function netClient(){
		if(envDB('BASE_MAINNET') == 'ETH')
			return BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'BSC')
			return BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'KLAY')
			return BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'MATIC')
			return BlockSDK::createPolygon(envDB('BLOCKSDK_TOKEN'));
	}
	public function getNft(Request $request){
		$mainnet = $this->getBackendEnv('BASE_MAINNET');
		$image_uri = $this->getBackendEnv('BASE_IMAGE_URI');
		$ipfs = $this->getBackendEnv('BASE_IPFS_GATEWAY');
		$blocksdk = substr($this->getBackendEnv('BLOCKSDK_TOKEN'),0,4) . "********";
		$multi_nft_contract_address = $this->getBackendEnv('MULTINFT_ADDRESS');
		$contract_address = $this->getBackendEnv('CONTRACT_ADDRESS');
		$contract_cache_time = $this->getBackendEnv('CACHE_TIME_NFT');
		
		$auth_nft_size = $this->getBackendEnv('UPLOAD_SIZE_AUTH_AUTHORS');
		$unauth_nft_size = $this->getBackendEnv('UPLOAD_SIZE_UNAUTH_AUTHORS');
		$profile_size = $this->getBackendEnv('UPLOAD_SIZE_PROFILE');
		$cover_size = $this->getBackendEnv('UPLOAD_SIZE_COVER');
		
		$address_filter = $this->getBackendEnv('UPLOAD_FILTER_ADDRESS');
		$filter = $this->getBackendEnv('UPLOAD_FILTER_TEXT');
		$ip = $this->getBackendEnv('UPLOAD_FILTER_IP');
		$layer = $this->getBackendEnv('UPLOAD_FILTER_LAYER');
		
		$categoryEnv = CategoryEnv::get();
		     $ownerAddress = [
            'erc721' => [
            ],
            'erc1155' => [
            ],
        ];
        $feeRate = [
            'erc721' => [
            ],
            'erc1155' => [
            ],
        ];
        $cancelFeeRate = [
            'erc721' => [
            ],
            'erc1155' => 0,
        ];
        $paused = [
            'erc721' => false,
            'erc1155' => false,
        ];


		$owner = $this->netClient()->getContractRead([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'method' => 'owner',
			'return_type' => 'address',
			'parameter_type' => [],
			'parameter_data' => []
		]);

		if(empty($owner['payload']) == false){
			$ownerAddress['erc721'] = $owner['payload']['result'];
		}

		$fee = $this->netClient()->getContractRead([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'method' => 'feeRate',
			'return_type' => 'uint256',
			'parameter_type' => [],
			'parameter_data' => []
		]);
		

		if(empty($fee['payload']) == false){
			$feeRate['erc721'] = $fee['payload']['result'];
		}

		$cancelFee = $this->netClient()->getContractRead([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'method' => 'cancelFeeRate',
			'return_type' => 'uint256',
			'parameter_type' => [],
			'parameter_data' => []
		]);

		if(empty($cancelFee['payload']) == false){
			$cancelFeeRate['erc721'] = $cancelFee['payload']['result'];
		}

		$pausedData = $this->netClient()->getContractRead([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'method' => 'paused',
			'return_type' => 'bool',
			'parameter_type' => [],
			'parameter_data' => []
		]);

		if(empty($pausedData['payload']) == false){
			$paused['erc721'] = $pausedData['payload']['result'];
		}



		$owner = $this->netClient()->getContractRead([
			'contract_address' => envDB('MULTINFT_ADDRESS'),
			'method' => 'owner',
			'return_type' => 'address',
			'parameter_type' => [],
			'parameter_data' => []
		]);

		if(empty($owner['payload']) == false){
			$ownerAddress['erc1155'] = $owner['payload']['result'];
		}

		$fee = $this->netClient()->getContractRead([
			'contract_address' => envDB('MULTINFT_ADDRESS'),
			'method' => 'feeRate',
			'return_type' => 'uint256',
			'parameter_type' => [],
			'parameter_data' => []
		]);

		if(empty($fee['payload']) == false){
			$feeRate['erc1155'] = $fee['payload']['result'];
		}
        
		
		return response()->json([
			'data' => [
				'contract' => [
					'owner' => $ownerAddress,
					'feeRate' => $feeRate,
					'cancelFeeRate' => $cancelFeeRate,
					'paused' => $paused,
				],
				'nft' => [
					'mainnet' => $mainnet,
					'image_uri' => $image_uri,
					'ipfs' => $ipfs,
					'blocksdk' => $blocksdk,
					'single_nft_contract_address' => $contract_address,
					'multi_nft_contract_address' => $multi_nft_contract_address,
					'contract_cache_time' => $contract_cache_time,
				],
				'upload' => [
					'auth_nft_size' => $auth_nft_size,
					'unauth_nft_size' => $unauth_nft_size,
					'profile_size' => $profile_size,
					'cover_size' => $cover_size,
					'address_filter' => $address_filter,
				],
				'filter' => [
					'source' => $filter,
				],
				'ip' => [
					'source' => $ip,
				],
				'layer' => [
					'source' => $layer,
				],
				'category' => $categoryEnv
			]
		]);
	}
	
	public function setFrontWeb($request){
		if(empty($request->name) == true){
			return response()->json([
				'error' => [
					'message' => '마켓 이름을 입력해주세요'
				],
			]);
		}else if(empty($request->mainnet) == true){
			return response()->json([
				'error' => [
					'message' => '메인넷을 선택해주세요'
				],
			]);
		}else if(empty($request->single_nft_contract_address) == true){
			return response()->json([
				'error' => [
					'message' => '계약 주소를 입력해주세요'
				],
			]);
		}else if(empty($request->multi_nft_contract_address) == true){
			return response()->json([
				'error' => [
					'message' => '계약 주소를 입력해주세요'
				],
			]);
		}else if(empty($request->wallet) == true){
			return response()->json([
				'error' => [
					'message' => '지갑을 선택해주세요'
				],
			]);
		}else if(empty($request->api_uri) == true){
			return response()->json([
				'error' => [
					'message' => 'API 주소를 입력 해주세요'
				],
			]);
		}else if(empty($request->exchange_api_uri) == true){
			return response()->json([
				'error' => [
					'message' => '거래소 API 주소를 입력 해주세요'
				],
			]);
		}
		
		$this->setFrontEnv('VUE_APP_NAME',$request->name);
		$this->setFrontEnv('VUE_APP_BASE_MAINNET',$request->mainnet);
		$this->setFrontEnv('VUE_APP_SINGLENFT_CONTRACT_ADDRESS',$request->single_nft_contract_address);
		$this->setFrontEnv('VUE_APP_MULTINFT_CONTRACT_ADDRESS',$request->multi_nft_contract_address);
		$this->setFrontEnv('VUE_APP_BASE_WALLET',$request->wallet);
		$this->setFrontEnv('VUE_APP_BASE_API_URI',$request->api_uri);
		$this->setFrontEnv('VUE_APP_BASE_EXCHANGE_API_URI',$request->exchange_api_uri);
		
		return false;
	}

	public function setNftEnv($request){
		if(empty($request->mainnet) == true){
			return response()->json([
				'error' => [
					'message' => '메인넷을 선택해주세요'
				],
			]);
		}else if(empty($request->image_uri) == true){
			return response()->json([
				'error' => [
					'message' => '이미지 주소 앞부분을 입력해 주세요'
				],
			]);
		}else if(empty($request->ipfs) == true){
			return response()->json([
				'error' => [
					'message' => 'IPFS 게이트웨이 주소를 입력해주세요'
				],
			]);
		}else if(empty($request->blocksdk) == true){
			return response()->json([
				'error' => [
					'message' => 'BLOCKSDK 토큰을 입력 해주세요'
				],
			]);
		}else if(empty($request->single_nft_contract_address) == true){
			return response()->json([
				'error' => [
					'message' => '스마트 계약 주소를 입력 해주세요'
				],
			]);
		}else if(empty($request->multi_nft_contract_address) == true){
			return response()->json([
				'error' => [
					'message' => '스마트 계약 주소를 입력 해주세요'
				],
			]);
		}else if(empty($request->contract_cache_time) == true){
			return response()->json([
				'error' => [
					'message' => '스마트 계약 캐시 타임 입력 해주세요'
				],
			]);
		}
		
		$this->setBackendEnv('BASE_MAINNET',$request->mainnet);
		$this->setBackendEnv('BASE_IMAGE_URI',$request->image_uri);
		$this->setBackendEnv('BASE_IPFS_GATEWAY',$request->ipfs);
		if(substr($request->blocksdk,-4) != '****'){
			$this->setBackendEnv('BLOCKSDK_TOKEN',$request->blocksdk);
		}
		$this->setBackendEnv('CONTRACT_ADDRESS',$request->single_nft_contract_address);
		$this->setBackendEnv('MULTINFT_ADDRESS',$request->multi_nft_contract_address);
		$this->setBackendEnv('CACHE_TIME_NFT',$request->contract_cache_time);
		
		return false;
	}
}