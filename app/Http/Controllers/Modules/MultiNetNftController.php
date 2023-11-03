<?php

namespace App\Http\Controllers\Modules;


use App\Models\NftLike;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Nft;

use App\Models\NftLikeCun;

use App\Models\Profile;

use App\Models\CacheNft;

use App\Models\LogTxVerify;

use App\Models\CategoryEnv;

use App\Models\PayToken;

use BlockSDK;
use IPFS;

trait MultiNetNftController {

	public function nets(){
		$result = [];

		if(empty(envDB('ETH_SINGLENFT_ADDRESS')) == false && empty(envDB('ETH_MULTINFT_ADDRESS')) == false){
			array_push($result,'ETH');
		}
		if(empty(envDB('BSC_SINGLENFT_ADDRESS')) == false && empty(envDB('BSC_MULTINFT_ADDRESS')) == false){
			array_push($result,'BSC');
		}
		if(empty(envDB('KLAY_SINGLENFT_ADDRESS')) == false && empty(envDB('KLAY_MULTINFT_ADDRESS')) == false){
			array_push($result,'KLAY');
		}
		if(empty(envDB('MATIC_SINGLENFT_ADDRESS')) == false && empty(envDB('MATIC_MULTINFT_ADDRESS')) == false){
			array_push($result,'MATIC');
		}

		return $result;
	}

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

	public function getCacheMultiNftSaleMap($net,$token_id,$seller_address){
		$result = $this->getCache($net . '_multinftsale2_' . $token_id . $seller_address);
		if(empty($result) == true){
			return $this->getMultiNftSaleMap($net,$token_id,$seller_address);
		}

		return [
			'isForSale' 	=> (bool)substr($result->data,0,64),
			'amount'		=> $this->bchexdec(substr($result->data,64,64)),
			'price'			=> $this->bchexdec(substr($result->data,128,64)),
			'tokenAddress'	=> "0x" . substr(substr($result->data,192,64),24),
		];
	}

	public function getMultiNftSaleMap($net,$token_id,$seller_address){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . "_MULTINFT_ADDRESS"),
			'method' => 'saleMap',
			'return_type' => 'bool',
			'parameter_type' => ['uint256','address'],
			'parameter_data' => [$token_id,$seller_address]
		]);

		$data = $data['payload'];

		$hex = substr($data['hex'],2);
		$this->cacheSave($net . '_multinftsale_' . $token_id . $seller_address,$hex);

		return [
			'isForSale' 	=> (bool)(int)$this->bchexdec(substr($hex,0,64)),
			'amount'		=> $this->bchexdec(substr($hex,64,64)),
			'price'			=> $this->bchexdec(substr($hex,128,64)),
			'tokenAddress'	=> "0x" . substr(substr($hex,192,64),24),
		];
	}

	public function getNfts($offset,$limit, $address){

		$tokens = [];
		$nets = $this->nets();

		foreach($nets as $net){

			$data = $this->netClient($net)->getNfts([
				'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
				'offset' => $offset,
				'limit' => $limit
			]);

			$payload = $data['payload'];

			foreach($payload['tokens'] as $token){
				$token = $this->getNftData($net,$token, $address);
				if($token == false){
					continue;
				}

				array_push($tokens,$token);
			}
		}

		return $tokens;
	}

	public function getMultiNfts($offset,$limit, $address){

		$tokens = [];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getMultiNft([
				'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
				'offset' => $offset,
				'limit' => $limit
			]);
			if(empty($data['payload']) == true){
				return false;
			}
			$payload = $data['payload'];

			foreach($payload['tokens'] as $token){
				$token = $this->getNewMultiNftData($net,$token, $address);
				if($token == false){
					continue;
				}

				array_push($tokens,$token);
			}
		}

		return $tokens;
	}

	public function getSaleNfts($seller_address,$offset,$limit, $address = null){

		$tokens = [];
		$totals = [0];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getSaleNfts([
				'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
				'seller_address' => $seller_address,
				'order_direction'  => 'desc',
				'offset'           => $offset,
				'limit'            => $limit
			]);

			$payload = $data['payload'];
			foreach($payload['sales'] as $token){
				$token = $this->getNftData($net,$token, $address);
				if($token == false){
					continue;
				}
				array_push($tokens,$token);
			}

			array_push($totals,$payload['total_sales']);
		}


		return [
			'total' => max($totals),
			'data' => $tokens
		];
	}

	public function getMultiSaleNfts($seller_address,$offset,$limit, $address = null){

		$tokens = [];
		$totals = [0];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getMultiSaleNfts([
				'contract_address' => envDB($net . "_MULTINFT_ADDRESS"),
				'seller_address' => $seller_address,
				'order_direction'  => 'desc',
				'offset'           => $offset,
				'limit'            => $limit
			]);

			$payload = $data['payload'];


			foreach($payload['sales'] as $token){
				if($token['token_amount'] == 0){
					continue;
				}
				$token = $this->getMultiNftData($net,$token, $address);
//				$token = $this->getMultiNftData($net,$token,$seller_address, $address);
				if($token == false){
					continue;
				}
				array_push($tokens,$token);
			}

			array_push($totals,$payload['total_sales']);
		}

		return [
			'total' => max($totals),
			'data' => $tokens
		];
	}

	public function getAuctionNfts($offset,$limit){


		$tokens = [];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getAuctionNfts([
				'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
				'order_by'         => 'end_time',
				'order_direction'  => 'asc',
				'offset'           => $offset,
				'limit'            => $limit
			]);

			$payload = $data['payload'];

			foreach($payload['auctions'] as $token){
				$token = $this->getNftData($net,$token);
				if($token == false){
					continue;
				}
				array_push($tokens,$token);
			}
		}

		return $tokens;
	}

	public function getCacheOffer($net,$token_id){
		$result = $this->getCache($net . '_offer_' . $token_id);
		if(empty($result) == true){
			return $this->getOffer($net,$token_id);
		}

		return $this->hexToOffer($result->data);
	}

	public function getOffer($net,$token_id){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'method' => 'offers',
			'return_type' => 'bool',
			'parameter_type' => ['uint256'],
			'parameter_data' => [$token_id]
		]);

		$data = $data['payload'];

		$hex = substr($data['hex'],2);

		$this->cacheSave($net . '_offer_' . $token_id,$hex);

		return $this->hexToOffer($hex);
	}

	public function getCacheBid($net,$token_id){
		$result = $this->getCache($net . '_bid_' . $token_id);
		if(empty($result) == true){
			return $this->getBid($net,$token_id);
		}

		return $this->hexToBid($result->data);
	}

	public function getBid($net,$token_id){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'method' => 'bids',
			'return_type' => 'bool',
			'parameter_type' => ['uint256'],
			'parameter_data' => [$token_id]
		]);

		$data = $data['payload'];

		$hex = substr($data['hex'],2);
		$this->cacheSave($net . '_bid_' . $token_id,$hex);

		return $this->hexToBid($hex);
	}

	public function getCacheListed($net,$token_id){
		$result = $this->getCache($net . '_listed_' . $token_id);
		if(empty($result) == true){
			return $this->listed($net,$token_id);
		}

		return $this->hexToBool($result->data);
	}

	public function listed($net,$token_id){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'method' => 'listedMap',
			'return_type' => 'bool',
			'parameter_type' => ['uint256'],
			'parameter_data' => [$token_id]
		]);

		$data = $data['payload'];

		$hex = substr($data['hex'],2);
		$this->cacheSave($net . '_listed_' . $token_id,$hex);

		return $this->hexToBool($hex);
	}

	public function getCachePrice($net,$token_id){
		$result = $this->getCache($net . '_price_' . $token_id);
		if(empty($result) == true){
			return $this->getPrice($net,$token_id);
		}

		return $this->hexToDec($result->data);
	}

	public function getPrice($net,$token_id){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'method' => 'price',
			'return_type' => 'uint256',
			'parameter_type' => ['uint256'],
			'parameter_data' => [$token_id]
		]);

		$data = $data['payload'];

		$hex = substr($data['hex'],2);
		$this->cacheSave($net . '_price_' . $token_id,$hex);

		return $this->hexToDec($hex);
	}

	public function getBids($net,$token_id, Request $req = null){
		$data = $this->netClient($net)->getNftBids([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'token_id' => $token_id,
			'rawtx' => true,
            'offset' => empty($req->offset) ? 0 : $req->offset,
            'limit' => 10
		]);

		if(empty($data['payload']) == true){
			return false;
		}

		$bids = [];
		foreach($data['payload']['bids'] as $bid){
			$rawtx = $bid['rawtx'];
			$bid['bidder'] = $this->getProfile($rawtx['from']);
			$bid['price'] = $this->rtrimPirce($rawtx['value']);

			if($bid['price'] == "0"){
				foreach($rawtx['logs'] as $log){
					if($log['topics'][0] != "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef" || count($log['topics']) != 3){
						continue;
					}

					$tokenAddress = $log['contract_address'];


					$value = $this->tokenAmount($tokenAddress,$this->bchexdec(substr($log['data'],2)));
					if($value > $bid['price']){
						$bid['price'] = $value;
					}

					break;
				}
			}else{
				$tokenAddress = "0x0000000000000000000000000000000000000000";
			}

			$payToken = PayToken::where('net',$net)->where('tokenAddress',$tokenAddress)->first();
			$bid['token'] = $payToken;

			array_push($bids,$bid);
		}

		return [
			'total' => $data['payload']['total_bids'],
			'data' => $bids
		];
	}

    public function getBidsByOffset(Request $request, $nft_id){
        $offset = empty($request->offset) ? 0 : $request->offset;
        $limit = 10;
        $nft = Nft::find($nft_id);
        if(empty($nft) == true){
            return response()->json([
                'error' => [
                    'message' => "존재하지 않는 NFT 입니다"
                ]
            ]);
        }else if($nft->interface == 'erc1155'){
            return response()->json([
                'error' => [
                    'message' => "잘못된 인터페이스 입니다"
                ]
            ]);
        }else if(empty($nft->net) == true || $nft->net == ""){
            return response()->json([
                'error' => [
                    'message' => "아직 판매되지 않은 상품입니다."
                ]
            ]);
        }

        $data = $this->getBids($nft->net, $nft->token_id, $request);
        return $data;
    }

	public function getTransfers($net,$token_id, Request $req = null){
		$data = $this->netClient($net)->getNftTransfers([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'token_id' => $token_id,
			'rawtx' => true,
			'offset' => empty($req->offset) ? 0 : $req->offset,
			'limit' => 10
		]);

        if (empty($data) == true) {
            return response()->json([
               'error' => "데이터가 없습니다."
            ]);
        }

        $total = $data['payload']['total_transfers'];
		$data = $data['payload'];


		$transfers = [];
		foreach($data['transfers'] as $transfer){
			$rawtx = $transfer['rawtx'];
			$rawtx['value'] = $this->rtrimPirce($rawtx['value']);
			$transfer['method'] = '';

			if(substr($rawtx['input'],0,10) == '0xda14cbbc'){

				//일반판매 발행
				$transfer['method'] = 'mint';
				$transfer['price'] = $this->bchexdec(substr($rawtx['input'],202,64));	//등록할 판매 가격
				$tokenaddr = '0x'.substr($rawtx['input'],34,40);

			}else if(substr($rawtx['input'],0,10) == '0x42f4997a'){//경매 발행

				$transfer['method'] = 'auctionMint';
				$transfer['price'] = $this->bchexdec(substr($rawtx['input'],266,64)); // 최소 입찰 시작 가격
				$tokenaddr = '0x'.substr($rawtx['input'],34,40);

			}else if(substr($rawtx['input'],0,10) == '0xd96a094a'){//구매

				$transfer['method'] = 'buy';
				$transfer['price'] = $rawtx['value'];//구매 가격

			}else if(substr($rawtx['input'],0,10) == '0xb9a2de3a'){//낙찰

				$transfer['method'] = 'endAuction';
				foreach($rawtx['logs'] as $log){
					if($log['topics'][0] != '0xc87036081503cc1fd53dc456ee0c40aef140882f77b06b4b4b554fee2b60816a'){
						continue;
					}

					$transfer['price'] = $this->bchexdec(substr($log['data'],-64,64)); // 최종 낙찰 금액
				}

			}else if($transfer['to'] == '0x0000000000000000000000000000000000000000'){
				$transfer['method'] = 'burn';
			}

			if($transfer['method'] != 'burn' && $transfer['method'] != ''){
				$price = $transfer['price'];
				if(empty($transfer['price']) == true || $transfer['price'] == "0" || empty($tokenaddr) == true){

					foreach($rawtx['logs'] as $log){
						if($log['topics'][0] != '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef' || $log['data'] == '0x'){
							continue;
						}
						if(empty($tokenaddr) == true){
							$tokenaddr = $log['contract_address'];
						}
						$price = bcadd($price,$this->bchexdec(substr($log['data'],2)));
					}
					if(empty($tokenaddr) == true){
						$tokenaddr = "0x0000000000000000000000000000000000000000";
					}
				}

				$payToken = PayToken::where('net',$net)->where('tokenAddress',$tokenaddr)->first();
				if($transfer['method'] != 'buy' && $tokenaddr != "0x0000000000000000000000000000000000000000"){
					for($i=0;$i<$payToken['decimals'];$i++){
						$price = bcdiv('' . $price,'10',$payToken['decimals']);
					}
				}else if($tokenaddr == "0x0000000000000000000000000000000000000000"){
					$price = bcdiv('' . $price,'1000000000000000000',18);
				}

				$transfer['price'] =  $this->rtrimPirce($price);
				$transfer['token'] = $payToken;
			}

			$transfer['from'] = $this->getProfile($transfer['from']);
			$transfer['to'] = $this->getProfile($transfer['to']);

			array_push($transfers,$transfer);
		}

		return [
            'transfers' => $transfers,
            'total' => $total
        ];
	}

	public function getCacheTokenInfo($net,$token_id,$id){
		$result = $this->getCache($net . '_tokeninfo_' . $id);
		if(empty($result) == true){
			return $this->getTokenInfo($net,$token_id,$id);
		}

		return json_decode($result->data,true);
	}

	public function getTokenInfo($net,$token_id,$id){
		$data = $this->netClient($net)->getNftInfo([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'token_id' => $token_id,
		]);
		if(empty($data['payload']) == true){
			print_r($token_id);
			exit;
		}
		$json = json_encode($data['payload']);
		$this->cacheSave($net . '_tokeninfo_' . $id,$json);

		return $data['payload'];
	}

	public function getCacheMultiNftTransfer($net,$token_id){
		$result = $this->getCache($net . '_multinfttransfer_' . $token_id);
		if(empty($result) == true){
			return $this->getMultiNftTransfers($net,$token_id)['transfers'];
		}

		return json_decode($result->data,true);
	}

	public function getMultiNftTransfers($net,$token_id, Request $req = null){
		$data = $this->netClient($net)->getMultiNftTransfers([
			'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
			'token_id' => $token_id,
			'rawtx' => true,
			'offset' => empty($req->offset) ? 0 : $req->offset,
			'limit' => 10
		]);
        $total = $data['payload']['total_transfers'];
		$data = $data['payload'];

		$transfers = [];

		foreach($data['transfers'] as $transfer){
			$rawtx = $transfer['rawtx'];
			$rawtx['value'] = $this->rtrimPirce($rawtx['value']);

			if(substr($rawtx['input'],0,10) == '0xdcbdb486'){//일반판매 발행

				$transfer['method'] = 'saleMint';
				$transfer['price'] = $this->bchexdec(substr($rawtx['input'],202,64));	//등록할 판매 가격
				$transfer['sell_amount'] = $this->bchexdec(substr($rawtx['input'],138,64));	//등록 개수
				$tokenAddress = '0x' . substr($rawtx['input'],34,40);

			}else if(substr($rawtx['input'],0,10) == '0x2afaca20'){//구매

				$transfer['method'] = 'buy';
				//$transfer['sell_amount'] = $this->bchexdec(substr($rawtx['input'],-64));	//구매 개수
				$transfer['price'] = bcmul($rawtx['value'],"1000000000000000000");//구매 가격

				$tokenAddress = "0x0000000000000000000000000000000000000000";
				if($transfer['price'] == "0"){
					foreach($rawtx['logs'] as $log){
						if($log['topics'][0] != "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef" || count($log['topics']) != 3){
							continue;
						}
						$tokenAddress = $log['contract_address'];
						$transfer['price'] = bcadd($transfer['price'],$this->bchexdec(substr($log['data'],2)));
					}
				}

			}else if($transfer['to'] == '0x0000000000000000000000000000000000000000'){
				$transfer['method'] = 'burn';
			}

			if(empty($transfer['method']) == false && $transfer['method'] != 'burn' && $transfer['method'] != ''){
				$payToken = PayToken::where('net',$net)->where('tokenAddress',$tokenAddress)->first();

				for($i=0;$i<$payToken['decimals'];$i++){
					$transfer['price'] = bcdiv($transfer['price'],'10',$payToken['decimals']);
				}


				$transfer['price'] =  $this->rtrimPirce($transfer['price']);
				$transfer['token'] = $payToken;
			}

			$transfer['from'] = $this->getProfile($transfer['from']);
			$transfer['to'] = $this->getProfile($transfer['to']);

			array_push($transfers,$transfer);
		}
		$json = json_encode($transfers);
		$this->cacheSave($net . '_multinfttransfer_' . $token_id,$json);

		return [
            'transfers' => $transfers,
            'total' => $total
        ];
	}

	public function getHoldNfts($owner_address,$offset,$limit, $address = null){

		$tokens = [];
		$totals = [0];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getOwnerNfts([
				'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
				'owner_address' => $owner_address,
				'offset' => $offset,
				'limit' => $limit
			]);

			$payload = $data['payload'];
			foreach($payload['tokens'] as $token){
				$token = $this->getNftData($net,$token, $address);
				if($token == false){
					continue;
				}
				array_push($tokens,$token);
			}

			array_push($totals,$payload['total_tokens']);
		}

		return [
			'total' => max($totals),
			'data' => $tokens
		];
	}
	public function getMultiHoldNfts($owner_address,$offset,$limit, $address = null){

		$tokens = [];
		$totals = [0];
		$nets = $this->nets();
		foreach($nets as $net){
			$data = $this->netClient($net)->getMultiNftContractOwner([
				'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
				'owner_address' => $owner_address,
				'offset' => $offset,
				'limit' => $limit
			]);

			$payload = $data['payload'];
			foreach($payload['tokens'] as $token){
				$token = $this->getMultiNftData($net,$token, $address);
//				$token = $this->getMultiNftData($net,$token,$owner_address, $address);
				if($token == false){
					continue;
				}
				array_push($tokens,$token);
			}

			array_push($totals,$payload['total_tokens']);
		}

		return [
			'total' => max($totals),
			'data' => $tokens
		];
	}

	public function getSingleNftSaleTokenAddress($net,$token_id){
		$data = $this->netClient($net)->getContractRead([
			'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
			'method' => 'saleTokenAddresses',
			'return_type' => 'address',
			'parameter_type' => ['uint256'],
			'parameter_data' => [$token_id]
		]);

		$data = $data['payload'];
		$this->cacheSave($net . '_SingleNftSaleTokenAddress_' . $token_id,json_encode($data['result']));

		return $data['result'];
	}



	public function getMultiTokenInfo($net,$token_id){
		$data = $this->netClient($net)->getMultiNftInfo([
			'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
			'token_id' => $token_id,
		]);

		$json = json_encode($data['payload']);
		$this->cacheSave($net . '_tokeninfo_' . $token_id,$json);

		return $data['payload'];
	}

	public function getCacheSingleNftSaleTokenAddress($net,$token_id){
		$result = $this->getCache($net . '_SingleNftSaleTokenAddress_' . $token_id);
		if(empty($result) == true){
			return $this->getSingleNftSaleTokenAddress($net,$token_id);
		}

		return json_decode($result->data,true);
	}

	public function getCacheTransfer($net,$tx_hash){
		$result = $this->getCache($net . '_transfer_' . md5($tx_hash));
		if(empty($result) == true){
			return $this->getTransferPrice($net,$tx_hash);
		}

		return json_decode($result->data,true);
	}

	public function getTransferPrice($net,$tx_hash){
		$data = $this->netClient($net)->getTransaction([
				"hash" => $tx_hash
			]);

		$data = $data['payload'];
		$transfers = [];

		if(substr($data['input'],0,10) == '0x6e4d3fb0'){//일반판매 발행
			$data['method'] = 'saleMint';
			$data['price'] = hexdec(substr($data['input'],138,64)) / 1000000000000000000;	//등록할 판매 가격

		}else if(substr($data['input'],0,10) == '0x2afaca20'){//구매
			$data['method'] = 'buy';
			$data['price'] = $data['value'];//구매 가격

		}else if($data['to'] == '0x0000000000000000000000000000000000000000'){
			$data['method'] = 'burn';
		}

		$data['from'] = $this->getProfile($data['from']);
		$data['to'] = $this->getProfile($data['to']);

		array_push($transfers,$data);

		$json = json_encode($transfers);
		$this->cacheSave('transfer_' . md5($tx_hash),$json);

		return $transfers;
	}

	public function getAllowance(Request $request,$contract){
		if(empty($request->net) == true){
			return response()->json([
				'error' => [
					'message' => "NET 이 누락되었습니다."
				]
			]);
		}

		$address = $request->address;

		$data = $this->netClient($request->net)->getContractRead([
			'contract_address' => $contract,
			'method' => 'allowance',
			'return_type' => 'bool',
			'parameter_type' => ['address','address'],
			'parameter_data' => [$address,$request->senderAddress]
		]);
		if(empty($data['payload']) == true){
			return response()->json([
				'error' => [
					'message' => "데이터가 없습니다."
				]
			]);
		}
		$data = $data['payload'];

		$hex = hexdec(substr($data['hex'],2));
		//$this->cacheSave('offer_' . $token_id,$hex);

		//return $hex;
		return response()->json([
			'hex' => $hex
		]);
	}


	public function getNFT(Request $request,$nft_id){
        $address = empty($request->address) ? null : $request->address;
		$nft = Nft::find($nft_id);
		if(empty($nft) == true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 NFT 입니다"
				]
			]);
		}

		$result = [
			'id'   => $nft->id,
			'net'   => $nft->net,

            'attributes' => json_decode($nft->attributes, true),
//            'attributes' => empty($nft->attributes) ? json_decode($nft->attributes, true) : null,

			'metadata_uri'   => "ipfs://" . $nft->id,
			'metadata_gateway_url' => envDB("BASE_IPFS_GATEWAY") . '/' .$nft->id,

			'image_uri'   => 'ipfs://' . '/' .$nft->ipfs_image_hash,
			'image_gateway_url'   => envDB("BASE_IPFS_GATEWAY") . '/' .$nft->id,

			'tx_hash' => $nft->tx_hash,
			'token_id' => $nft->token_id,
			'interface' => $nft->interface,
			'token_amount' => $nft->token_amount,

			'name' => $nft->name,
			'description' => $nft->description,

			'year_creation' => $nft->year_creation,
            'thumbnail' => envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->thumbnail
		];


		if(substr($nft->file_name,-4) == '.mp4'){
			if(empty(envDB('IS_AWS_S3')) == true){
				$result['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$result['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$result['video_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}else if(Str::lower(substr($nft->file_name,-4)) == '.mp3'){
            if(empty(envDB('IS_AWS_S3')) == true){
                $result['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
                $result['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false){
                $result['music_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
            }

            $result['is_music'] = true;
		}else{
            if(empty(envDB('IS_AWS_S3')) == true){
                $result['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
                $result['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false){
                $result['image_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
            }
        }


		$result['creator'] = $this->getProfile($nft->creator_address);
		$result['total_creation'] = Nft::where('creator_address',$nft->creator_address)->count();

		if(empty($nft->token_id) == false && $result['interface'] == 'erc721'){
			$data = $this->netClient($nft->net)->getNftInfo([
				'contract_address' => envDB($nft->net . '_SINGLENFT_ADDRESS'),
				'token_id' => $nft->token_id,
			]);

			$nftToken = $data['payload'];
			$result['owner'] = $this->getProfile($nftToken['owner']);

			$result['bids'] = $this->getBids($nft->net,$nft->token_id);

			$result['listed'] = $this->listed($nft->net,$nft->token_id);
			if($result['listed'] == false){
				$result['offer'] = $this->getOffer($nft->net,$nft->token_id);

				if($result['offer']['isForSale'] == true){
					$result['bid'] = $this->getBid($nft->net,$nft->token_id);
				}else if($result['offer']['isForSale'] == false){
					$this->cacheSave($nft->net . '_price_' . $nft->token_id,0);
				}
				$tokenAddress = $result['offer']['tokenAddress'];
				$result['price'] = $result['offer']['minValue'];
			}else{
				$tokenAddress = $this->getSingleNftSaleTokenAddress($nft->net,$nft->token_id);
				$result['price'] = $this->getPrice($nft->net,$nft->token_id);

				$cache = CacheNft::find('offer_' . $nft->token_id);
				if(empty($cache) == false){
					$cache->delete();
				}
			}

			$payToken = PayToken::where('net',$nft->net)->where('tokenAddress',$tokenAddress)->first();
			for($i=0;$i<$payToken['decimals'];$i++){
				$result['price'] = bcdiv('' . $result['price'],'10',$payToken['decimals']);
				if(empty($result['offer']['minValue']) == false){
					$result['offer']['minValue'] = bcdiv('' . $result['offer']['minValue'],'10',$payToken['decimals']);
				}
				if(empty($result['offer']['isForSale']) == false){
					$result['bid']['value'] = bcdiv('' . $result['bid']['value'],'10',$payToken['decimals']);
				}
			}
			if(empty($result['offer']['isForSale']) == false){
				$result['offer']['minValue'] = $this->rtrimPirce($result['offer']['minValue']);
				$result['bid']['value'] = $this->rtrimPirce($result['bid']['value']);
			}
			$result['price'] = $this->rtrimPirce($result['price']);
			$result['token'] = $payToken;
            /*
             * 2022. 11. 21 like func patch
             * */
			$result['like'] = $this->likeCun($nft->id);
            $result['isLiked'] = $this->isLiked($address, $nft->id);
//			$result['like'] = $this->likeCun($nft->token_id);

            $transfers_temp = $this->getTransfers($nft->net,$nft->token_id);
			$result['transfers'] = $transfers_temp['transfers'];
			$result['transfers_total'] = $transfers_temp['total'];
            unset($transfers_temp);
		}else if(empty($nft->token_id) == false && $result['interface'] == 'erc1155'){
			$ownerList = $this->netClient($nft->net)->getMultiNftOwnerList([
				'contract_address' => envDB($nft->net . "_MULTINFT_ADDRESS"),
				'token_id' => $nft->token_id,
				'limit' => 12,
        	]);

			$ownlist = [];
			foreach($ownerList['payload'][0]['owner'] as $item){
				$owner = $this->packingMultiNftOwnerOf($nft,$item);

				array_push($ownlist,$owner);
			}

			$result['owner'] = $ownlist;
			$result['total_owner'] = $ownerList['payload'][0]['total_owner'];
			$result['price'] = 0;
            /*
             * 2022. 11. 21 like func patch
             * */
			$result['like'] = $this->likeCun($nft->id);
            $result['isLiked'] = $this->isLiked($address, $nft->id);
//			$result['like'] = $this->likeCun($nft->token_id);

            $transfers_temp = $this->getMultiNFTTransfers($nft->net,$nft->token_id);
			$result['transfers'] = $transfers_temp['transfers'];
			$result['transfers_total'] = $transfers_temp['total'];
            unset($transfers_temp);
		}else{
			$result['owner'] = $result['creator'];
			$result['like'] = 0;
            $result['isLiked'] = false;
		}

		return response()->json($result);
	}

	public function GetOwners(Request $request,$nft_id){
        $offset = empty($request->offset) ? 0 : $request->offset;
        $limit = 10;
		$nft = Nft::find($nft_id);
		if(empty($nft) == true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 NFT 입니다"
				]
			]);
		}else if($nft->interface != 'erc1155'){
			return response()->json([
				'error' => [
					'message' => "잘못된 인터페이스 입니다"
				]
			]);
		}

//		$offset = $request->input('offset',0);
        $offset = $request->input('offset',$offset);
//		$limit = $request->input('limit',12);
//        $limit = $request->input('limit',$limit);

		$ownerList = $this->netClient($nft->net)->getMultiNftOwnerList([
			'contract_address' => envDB($nft->net . "_MULTINFT_ADDRESS"),
			'token_id' => $nft->token_id,
			'offset'			=> $offset,
			'limit'				=> $limit,
		]);

		$owners = [];
		foreach($ownerList['payload'][0]['owner'] as $item){
			$owner = $this->packingMultiNftOwnerOf($nft,$item);

			array_push($owners,$owner);
		}

		return response()->json([
			'data' => $owners,
			'total'=> $ownerList['payload'][0]['total_owner'] - 1,
		]);
	}

    public function getTransfersByOffset(Request $request, $nft_id){

        $nft = Nft::find($nft_id);
        if (empty($nft) == true){
            return response()->json([
                'error' => "nft 데이터가 존재하지 않습니다."
            ]);
        }else if(empty($nft->token_id) == true || $nft->token_id == null){
            return response()->json([
                'error' => "거래가 성사되지 않아 token_id가 존재하지 않습니다."
            ]);
        }

        if ($nft->interface == "erc1155"){
            //다중 판매
            $data = $this->getMultiNftTransfers($nft->net, $nft->token_id, $request);
        }else {
            //일반 판매
            $data = $this->getTransfers($nft->net, $nft->token_id, $request);
        }



        return response()->json([
//            'nft' => $nft,
            'transfer' => $data['transfers'],
            'total' => $data['total']
        ]);
    }


	public function packingMultiNftOwnerOf($nft,$item){
		$saleMap = $this->getMultiNftSaleMap($nft->net,$nft->token_id,$item['address']);
		$owner  = $this->getProfile($item['address']);
		$owner['amount'] = $item['token_amount'];
		$payToken = PayToken::where('net',$nft->net)->where('tokenAddress',ltrim($saleMap['tokenAddress']))->first();

		if($saleMap['isForSale'] == true){
			$owner['sell_amount'] = $saleMap['amount'];
			$owner['price'] = $saleMap['price'];
			for($i=0;$i<$payToken['decimals'];$i++){
				$owner['price'] = bcdiv($owner['price'],10,$payToken['decimals']);
			}

			$owner['token'] = $payToken;
		}

		return $owner;
	}

	public function txVerify(Request $request,$nft_id){
		$nft = Nft::find($nft_id);
		if(empty($nft) == true){
			return response()->json([
				'error' => [
					'message' => "존재하지 않는 NFT 입니다"
				]
			]);
		}else if(empty($request->tx_hash) == true){
			return response()->json([
				'error' => [
					'message' => "트랜잭션 해시를 입력해주세요"
				]
			]);
		}else if(empty($request->net) == true || ($request->net != 'ETH' && $request->net != 'BSC' && $request->net != 'KLAY' && $request->net != 'MATIC')){
			return response()->json([
				'error' => [
					'message' => "네트워크를 선택해 주세요"
				]
			]);
		}

		$net = strtoupper($request->net);
		if($net != 'ETH' && $net != 'BSC' && $net != 'KLAY' && $net != 'MATIC'){
			return response()->json([
				'error' => [
					'message' => "지원하지 않은 네트워크 입니다."
				]
			]);
		}

		LogTxVerify::firstOrCreate([
			'tx_hash' => $request->tx_hash
		]);

		for($i=0;$i<300;$i++){
			$result = $this->netClient($net)->getTransaction([
				'hash' => $request->tx_hash
			]);

			if(empty($result['payload']) == false && empty($result['payload']['confirmations']) == false){
				break;
			}

			sleep(2);
		}

		if(empty($result['payload']) == true){
			return response()->json([
				'error' => [
					'message' => "트랜잭션을 찾을수 없습니다"
				]
			]);
		}

		$transaction = $result['payload'];
		if(empty($transaction['logs']) == true || $transaction['status'] == 0){
			return response()->json([
				'error' => [
					'message' => "컨트렉트 실행을 실패한 트랜잭션 입니다"
				]
			]);
		}


		$verify = false;
		$token_id = 0;
		$interface = 'erc721';
		foreach($transaction['logs'] as $log){
			if(strtolower($log['contract_address']) != strtolower(envDB($net . '_SINGLENFT_ADDRESS')) && strtolower($log['contract_address']) != strtolower(envDB($net . '_MULTINFT_ADDRESS'))){
				continue;
			}

			if($log['topics'][0] != '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef' && $log['topics'][0] != '0xc3d58168c5ae7397731d063d5bbf3d657854427343f4c083240f7aacaa2d0f62'){
                continue;
            }elseif ($log['topics'][0] == '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'){
                $token_id = hexdec($log['topics'][3]);
                $verify = true;
                $interface = 'erc721';
            }elseif($log['topics'][0] == '0xc3d58168c5ae7397731d063d5bbf3d657854427343f4c083240f7aacaa2d0f62'){
                $token_id = hexdec(substr($log['data'],2,64));
                $amount = hexdec(substr($log['data'],66,64));
                $verify = true;
                $interface = 'erc1155';
				$nft->token_amount  = $amount;
            }
		}


		if($verify == false || $transaction['from'] != $nft->creator_address){
			return response()->json([
				'error' => [
					'message' => "검증에 실패하였습니다"
				]
			]);
		}

		$nft->tx_hash  = $request->tx_hash;
		$nft->token_id = $token_id;
		$nft->year_creation = substr($transaction['datetime'],0,4);
		$nft->interface  = $interface;
		$nft->net  = $net;
		$nft->save();

		return response()->json([
			'verify' => true,
			'token_id' => $token_id,
			'year_creation' => $nft->year_creation,
			'transaction' => $transaction
		]);
	}

	public function getNftData($net,$tokenInfo, $address = null){
		$nft = Nft::where('net',$net)->where('token_id',$tokenInfo['token_id'])->where('interface','erc721')->first();
		if(empty($nft) == true){
			return false;
		}
		$tokenInfo['id'] = $nft->id;
		$tokenInfo['net'] = $nft->net;
		$tokenInfo['name'] = $nft->name;
		$tokenInfo['description'] = $nft->description;
		if(empty($nft->thumbnail) == false){
			$tokenInfo['thumbnail'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->thumbnail;
		}

		if(substr($nft->file_name,-4) == '.mp4'){
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['video_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}
        else if(substr($nft->file_name,-4) == '.mp3'){
            if(empty(envDB('IS_AWS_S3')) == true){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false){
                $tokenInfo['music_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
            }

            $tokenInfo['is_music'] = true;
        }
        else{
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['image_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}

		$tokenInfo['owner'] = $this->getProfile($tokenInfo['owner']);

        /*
         * 2022. 11. 21 좋아요 기능 패치
         * */
		$tokenInfo['like']  = $this->likeCun($nft->id);
//		$tokenInfo['like']  = $this->likeCun($tokenInfo['token_id']);

        $tokenInfo['isLiked'] = $this->isLiked($address, $nft->id);

		$tokenInfo['listed'] = $this->getCacheListed($nft->net,$nft->token_id);
		if($tokenInfo['listed'] == false){
			$tokenInfo['offer'] = $this->getCacheOffer($nft->net,$nft->token_id);

			if($tokenInfo['offer']['isForSale'] == true){
				$tokenInfo['bid'] = $this->getCacheBid($nft->net,$nft->token_id);
			}
			$tokenAddress = $tokenInfo['offer']['tokenAddress'];
			$tokenInfo['price'] = $tokenInfo['offer']['minValue'];

		}else{
			$tokenAddress = $this->getCacheSingleNftSaleTokenAddress($nft->net,$nft->token_id);
			$tokenInfo['price'] = $this->getCachePrice($nft->net,$nft->token_id);
		}

		$payToken = PayToken::where('net',$nft->net)->where('tokenAddress',$tokenAddress)->first();

		for($i=0;$i<$payToken['decimals'];$i++){
			$tokenInfo['price'] = bcdiv('' . $tokenInfo['price'],'10',$payToken['decimals']);

			if(empty($tokenInfo['offer']['isForSale']) == false){
				$tokenInfo['offer']['minValue'] = bcdiv('' . $tokenInfo['offer']['minValue'],'10',$payToken['decimals']);
			}
		}
		if(empty($tokenInfo['offer']['minValue']) == false){
			$tokenInfo['offer']['minValue'] = $this->rtrimPirce($tokenInfo['offer']['minValue']);
		}

		$tokenInfo['price'] =  $this->rtrimPirce($tokenInfo['price']);
		$tokenInfo['token']  = $payToken;

		return $tokenInfo;
	}

	public function getMultiNftData($net,$tokenInfo,$address){
		$nft = Nft::where('net',$net)->where('token_id',$tokenInfo['token_id'])->where('interface','erc1155')->first();
		if(empty($nft) == true){
			return false;
		}

		$test = $this->getCacheMultiNftTransfer($nft->net,$tokenInfo['token_id']);


		if(!empty($test)){
			foreach($test as $create){
				if($create['from']['address'] == '0x0000000000000000000000000000000000000000'){
					$profile = $create['to']['address'];
					$tokenInfo['token_amount'] = $create['token_amount'];
				}
				foreach($create['rawtx']['logs'] as $log){
					if($log['topics'][0] != '0xd8b419dcb55349414000518e58bc0ec29a3419a418cc9ab706cd9db1260cb2f4'){
						continue;
					}

					$tokenInfo['price'] = hexdec(substr($log['data'],2,64)) / 1000000000000000000; // 최종 낙찰 금액
				}
			}
		}


		$tokenInfo['id'] = $nft->id;
		$tokenInfo['net'] = $nft->net;
		$tokenInfo['name'] = $nft->name;
		$tokenInfo['description'] = $nft->description;
		$tokenInfo['interface'] = $nft->interface;
        $tokenInfo['thumbnail'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->thumbnail;


		if(substr($nft->file_name,-4) == '.mp4'){
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['video_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}
        else if(substr($nft->file_name,-4) == '.mp3'){
            if(empty(envDB('IS_AWS_S3')) == true){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false){
                $tokenInfo['music_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
            }

            $tokenInfo['is_music'] = true;
        }
        else{
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['image_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}

		$transfer = $this->getCacheTransfer($nft->net,$nft->tx_hash);
		$createMap = $this->getCacheMultiNftSaleMap($nft->net,$nft->token_id,$transfer[0]['from']['address']);
		$payToken = PayToken::where('net',$nft->net)->where('tokenAddress',ltrim($createMap['tokenAddress']))->first();
		if($createMap['isForSale'] == true){
			$creator['sell_amount'] = $this->bchexdec(substr($transfer[0]['input'],138,64));
			$creator['price'] = $this->bchexdec(substr($transfer[0]['input'],202,64));
			for($i=0;$i<$payToken['decimals'];$i++){
				$creator['price'] = bcdiv($creator['price'],10,$payToken['decimals']);
			}
			$tokenInfo['price'] = $creator['price'];
			//토큰결제일경우
			//if($createMap['tokenAddress'] != '0x0000000000000000000000000000000000000000'){
				$tokenInfo['token'] = $payToken;
			//}
		}
		$tokenInfo['creator'] = $this->getProfile($nft['creator_address']);
		unset($tokenInfo['owner']);
		//$tokenInfo['owner'] = $this->getProfile($tokenInfo['owner']);

		$tokenInfo['price'] =  $this->rtrimPirce($tokenInfo['price']);

        /*
         * 2022. 11. 21 like func patch
         * */
        $tokenInfo['like']  = $this->likeCun($nft->id);
        $tokenInfo['isLiked'] = $this->isLiked($address, $nft->id);

//		$tokenInfo['like']  = $this->likeCun($tokenInfo['token_id']);
		//$tokenInfo['price'] = bcdiv($this->bchexdec(substr($data,128)),1000000000000000000,8);

		return $tokenInfo;
	}


	public function getNewMultiNftData($net,$tokenInfo, $address = null){
		$nft = Nft::where('net',$net)->where('token_id',$tokenInfo['token_id'])->where('interface','erc1155')->first();
		if(empty($nft) == true){
			return false;
		}



		$tokenInfo['id'] = $nft->id;
		$tokenInfo['name'] = $nft->name;
		$tokenInfo['net'] = $nft->net;
		$tokenInfo['description'] = $nft->description;
		$tokenInfo['interface'] = $nft->interface;
        $tokenInfo['thumbnail'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->thumbnail;

		if(substr($nft->file_name,-4) == '.mp4'){
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['video_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['video_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}
        else if(substr($nft->file_name,-4) == '.mp3'){
            if(empty(envDB('IS_AWS_S3')) == true){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
                $tokenInfo['music_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
            }else if(empty(envDB('IS_AWS_S3')) == false){
                $tokenInfo['music_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
            }

            $tokenInfo['is_music'] = true;
        }
        else{
			if(empty(envDB('IS_AWS_S3')) == true){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->file_name)) == true ){
				$tokenInfo['image_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->file_name);
			}else if(empty(envDB('IS_AWS_S3')) == false){
				$tokenInfo['image_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->file_name;
			}
		}

		$transfer = $this->getCacheTransfer($nft->net,$nft->tx_hash);

		$tokenAddress = '0x' . substr($transfer[0]['input'],34,40);
		$payToken = PayToken::where('net',$nft->net)->where('tokenAddress',$tokenAddress)->first();

		$tokenInfo['sell_amount'] = $this->bchexdec(substr($transfer[0]['input'],138,64));
		$tokenInfo['price'] = $this->bchexdec(substr($transfer[0]['input'],202,64));
		for($i=0;$i<$payToken['decimals'];$i++){
			$tokenInfo['price'] = bcdiv($tokenInfo['price'],10,$payToken['decimals']);
		}
		$tokenInfo['token'] = $payToken;


		$tokenInfo['price'] = $this->rtrimPirce($tokenInfo['price']);

		$tokenInfo['creator'] = $this->getProfile($nft->creator_address);
		$tokenInfo['token_amount'] = bcsub($tokenInfo['mint_amount'],$tokenInfo['burn_amount']);

        /*
         * 2022. 11. 21 like func patch
         * */
		$tokenInfo['like']  = $this->likeCun($nft->id);
        $tokenInfo['isLiked'] = $this->isLiked($address, $nft->id);

//		$tokenInfo['like']  = $this->likeCun($tokenInfo['token_id']);
		//$tokenInfo['amount'] = bcdiv($this->bchexdec('38d7ea4c68000'),1000000000000000000,8);

		return $tokenInfo;
	}

	public function likeCun($token_id){
		$nft_like_cun = NftLikeCun::find($token_id);
		if(empty($nft_like_cun) == true){
			return 0;
		}

		return $nft_like_cun['cun'];
	}

    public function isLiked($address, $nft_id){
        if ($address == null) return false;
        $where = [];
        $where[] = ['address' , Str::lower($address)];
        $where[] = ['token_id' , $nft_id];

        $nft_like = NftLike::where($where);
        if ($nft_like->exists() == false){
            return  false;
        }

        return true;
    }

}
