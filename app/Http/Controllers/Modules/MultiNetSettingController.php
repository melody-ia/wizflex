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
trait MultiNetSettingController {
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

    public function getNft(Request $request){
        $mainnet = $this->getBackendEnv('BASE_MAINNET');
        $image_uri = $this->getBackendEnv('BASE_IMAGE_URI');
        $ipfs = $this->getBackendEnv('BASE_IPFS_GATEWAY');
        $blocksdk = substr($this->getBackendEnv('BLOCKSDK_TOKEN'),0,4) . "********";
        $contract_cache_time = $this->getBackendEnv('CACHE_TIME_NFT');



        $single_nft_contract_address = $this->getBackendEnv('CONTRACT_ADDRESS');
        $eth_single_nft_contract_address = $this->getBackendEnv('ETH_SINGLENFT_ADDRESS');
        $klay_single_nft_contract_address = $this->getBackendEnv('KLAY_SINGLENFT_ADDRESS');
        $bsc_single_nft_contract_address = $this->getBackendEnv('BSC_SINGLENFT_ADDRESS');
        $matic_single_nft_contract_address = $this->getBackendEnv('MATIC_SINGLENFT_ADDRESS');

        $multi_nft_contract_address = $this->getBackendEnv('MULTINFT_ADDRESS');
        $eth_multi_nft_contract_address = $this->getBackendEnv('ETH_MULTINFT_ADDRESS');
        $klay_multi_nft_contract_address = $this->getBackendEnv('KLAY_MULTINFT_ADDRESS');
        $bsc_multi_nft_contract_address = $this->getBackendEnv('BSC_MULTINFT_ADDRESS');
        $matic_multi_nft_contract_address = $this->getBackendEnv('MATIC_MULTINFT_ADDRESS');

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
            'erc1155' => [
            ],
        ];
        $paused = [
            'erc721' => [
            ],
            'erc1155' => [
            ],
        ];

        $nets = $this->nets();
        foreach($nets as $net){
            $owner = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
                'method' => 'owner',
                'return_type' => 'address',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($owner['payload']) == false){
                $ownerAddress['erc721'][$net] = $owner['payload']['result'];
            }

            $fee = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
                'method' => 'feeRate',
                'return_type' => 'uint256',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($fee['payload']) == false){
                $feeRate['erc721'][$net] = $fee['payload']['result'];
            }

            $cancelFee = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
                'method' => 'cancelFeeRate',
                'return_type' => 'uint256',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($cancelFee['payload']) == false){
                $cancelFeeRate['erc721'][$net] = $cancelFee['payload']['result'];
            }

            $pausedData = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_SINGLENFT_ADDRESS'),
                'method' => 'paused',
                'return_type' => 'bool',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($pausedData['payload']) == false){
                $paused['erc721'][$net] = $pausedData['payload']['result'];
            }



            $owner = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
                'method' => 'owner',
                'return_type' => 'address',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($owner['payload']) == false){
                $ownerAddress['erc1155'][$net] = $owner['payload']['result'];
            }

            $fee = $this->netClient($net)->getContractRead([
                'contract_address' => envDB($net . '_MULTINFT_ADDRESS'),
                'method' => 'feeRate',
                'return_type' => 'uint256',
                'parameter_type' => [],
                'parameter_data' => []
            ]);

            if(empty($fee['payload']) == false){
                $feeRate['erc1155'][$net] = $fee['payload']['result'];
            }
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
                    'contract_cache_time' => $contract_cache_time,

                    'eth_single_nft_contract_address' => $eth_single_nft_contract_address,
                    'klay_single_nft_contract_address' => $klay_single_nft_contract_address,
                    'bsc_single_nft_contract_address' => $bsc_single_nft_contract_address,
                    'matic_single_nft_contract_address' => $matic_single_nft_contract_address,

                    'eth_multi_nft_contract_address' => $eth_multi_nft_contract_address,
                    'klay_multi_nft_contract_address' => $klay_multi_nft_contract_address,
                    'bsc_multi_nft_contract_address' => $bsc_multi_nft_contract_address,
                    'matic_multi_nft_contract_address' => $matic_multi_nft_contract_address,
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
                    'message' => __('error.market_name')
                ],
            ]);
        }else if(empty($request->wallet) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.market_wallet')
                ],
            ]);
        }else if(empty($request->api_uri) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.api_uri')
                ],
            ]);
        }else if(empty($request->exchange_api_uri) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.exchange_api_uri')
                ],
            ]);
        }

        $this->setFrontEnv('VUE_APP_NAME',$request->name);


        if(empty($request->eth_single_nft_contract_address) == false && empty($request->eth_multi_nft_contract_address) == false){
            $this->setFrontEnv('IS_ETH_NET',"1");
        }else{
            $this->setFrontEnv('IS_ETH_NET',"0");
        }

        if(empty($request->klay_single_nft_contract_address) == false && empty($request->klay_multi_nft_contract_address) == false){
            $this->setFrontEnv('IS_KLAY_NET',"1");
        }else{
            $this->setFrontEnv('IS_KLAY_NET',"0");
        }

        if(empty($request->bsc_single_nft_contract_address) == false && empty($request->bsc_multi_nft_contract_address) == false){
            $this->setFrontEnv('IS_BSC_NET',"1");
        }else{
            $this->setFrontEnv('IS_BSC_NET',"0");
        }

        $this->setFrontEnv('VUE_APP_ETH_SINGLENFT_CONTRACT_ADDRESS',$request->eth_single_nft_contract_address);
        $this->setFrontEnv('VUE_APP_KLAY_SINGLENFT_CONTRACT_ADDRESS',$request->klay_single_nft_contract_address);
        $this->setFrontEnv('VUE_APP_BSC_SINGLENFT_CONTRACT_ADDRESS',$request->bsc_single_nft_contract_address);
        $this->setFrontEnv('VUE_APP_MATIC_SINGLENFT_CONTRACT_ADDRESS',$request->maitc_single_nft_contract_address);
        $this->setFrontEnv('VUE_APP_ETH_MULTINFT_CONTRACT_ADDRESS',$request->eth_multi_nft_contract_address);
        $this->setFrontEnv('VUE_APP_KLAY_MULTINFT_CONTRACT_ADDRESS',$request->klay_multi_nft_contract_address);
        $this->setFrontEnv('VUE_APP_BSC_MULTINFT_CONTRACT_ADDRESS',$request->bsc_multi_nft_contract_address);
        $this->setFrontEnv('VUE_APP_MATIC_MULTINFT_CONTRACT_ADDRESS',$request->matic_multi_nft_contract_address);

        $this->setFrontEnv('VUE_APP_BASE_WALLET',$request->wallet);
        $this->setFrontEnv('VUE_APP_BASE_API_URI',$request->api_uri);
        $this->setFrontEnv('VUE_APP_BASE_EXCHANGE_API_URI',$request->exchange_api_uri);

        return false;
    }


    public function setNftEnv($request){
        if(empty($request->image_uri) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.image_uri')
                ],
            ]);
        }else if(empty($request->ipfs) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.ipfs')
                ],
            ]);
        }else if(empty($request->blocksdk) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.blocksdk')
                ],
            ]);
        }else if(empty($request->contract_cache_time) == true){
            return response()->json([
                'error' => [
                    'message' => __('error.contract_cache_time')
                ],
            ]);
        }

        $this->setBackendEnv('BASE_MAINNET',$request->mainnet);
        $this->setBackendEnv('BASE_IMAGE_URI',$request->image_uri);
        $this->setBackendEnv('BASE_IPFS_GATEWAY',$request->ipfs);
        if(substr($request->blocksdk,-4) != '****'){
            $this->setBackendEnv('BLOCKSDK_TOKEN',$request->blocksdk);
        }

        $this->setBackendEnv('ETH_SINGLENFT_ADDRESS',$request->eth_single_nft_contract_address);
        $this->setBackendEnv('KLAY_SINGLENFT_ADDRESS',$request->klay_single_nft_contract_address);
        $this->setBackendEnv('BSC_SINGLENFT_ADDRESS',$request->bsc_single_nft_contract_address);
        $this->setBackendEnv('MATIC_SINGLENFT_ADDRESS',$request->matic_single_nft_contract_address);
        $this->setBackendEnv('ETH_MULTINFT_ADDRESS',$request->eth_multi_nft_contract_address);
        $this->setBackendEnv('KLAY_MULTINFT_ADDRESS',$request->klay_multi_nft_contract_address);
        $this->setBackendEnv('BSC_MULTINFT_ADDRESS',$request->bsc_multi_nft_contract_address);
        $this->setBackendEnv('MATIC_MULTINFT_ADDRESS',$request->matic_multi_nft_contract_address);

        $this->setBackendEnv('CACHE_TIME_NFT',$request->contract_cache_time);

        return false;
    }
}
