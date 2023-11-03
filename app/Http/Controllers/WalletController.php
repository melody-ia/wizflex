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

class WalletController extends Controller
{
    public function __construct()
    {

    }

    public function netClient($net){
        if($net == 'ETH')
            return  BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
        if($net == 'BSC' || $net == "BNB")
            return  BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
        if($net == 'KLAY')
            return  BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
    }

    public function getBalance(Request $req, $address){
        if (empty($req->net) == true){
            return response()->json([
               'error' => 'net data is required'
            ]);
        }
        if (empty($address) == true){
            return response()->json([
                'error' => 'address data is required'
            ]);
        }


        $data = $this->netClient($req->net)->getAddressBalance(['address'  => Str::lower($address) ]);

        if (empty($data['payload']) == true){
            return response()->json([
                'error' => 'getBalance API ERROR'
            ]);
        }

        return response()->json([
           'data'=> $data['payload']
        ]);
    }
}
