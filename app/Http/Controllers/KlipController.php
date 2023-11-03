<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use BlockSDK;
class KlipController extends Controller
{

    public function klipBalance(Request $req, $address){

//	    print_r();
//	    exit;
        if (empty($address) === true){
            return response()->json([
                "error" => "require address"
            ]);
        }

        $klay = BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
        $data = $klay->getAddressBalance(['address'  => Str::lower($address) ]);

        if ($data['state']['success'] == false){
            return response()->json([
                "error" => "not data"
            ]);
        }
        return response()->json([
            'data' => $data['payload']
        ]);

    }


    public function contractExec(Request $req){
        if (empty($req->bapp) == true){
            return response()->json([
                'error' => "bapp값은 필수입니다."
            ]);
        }
        if (empty($req->transaction) == true){
            return response()->json([
                'error' => "bapp값은 필수입니다."
            ]);
        }
        if (empty($req->type) == true){
            return response()->json([
                'error' => "bapp값은 필수입니다."
            ]);
        }

        $data["bapp"] = $req->bapp;
        $data["transaction"] = $req->transaction;
        $data["type"] = $req->type;

////        return $data;
////
//        $data['bapp']['name'] = "NFT 마켓 솔루션";
//        $data['transaction']['abi'] = '{"inputs":[{"name":"fqkql","type":"address"},{"name":"vgqg54","type":"string"},{"name":"24tou","type":"address"},{"name":"o2mpko","type":"uint256"}],"name":"mint","outputs":[],"stateMutability":"nonpayable","type":"function","signature":"0xda14cbbc"}';
//        $data['transaction']['from'] = "0xca0af6c8a842d433fada35545ac52c44b40f44f6";
//        $data['transaction']['params'] = '["0x0000000000000000000000000000000000000000","QmakhdZPWqZhWDcSc85vc4h196W4KZGwnmTy2e7oo1BouT","0xca0af6c8a842d433fada35545ac52c44b40f44f6","3000000000000000000"]';
//        $data['transaction']['to'] = "0x423149e306154eb5ea8da64d7676e0b2343234b8";
//        $data['transaction']['value'] = "0";
//        $data['type'] = "execute_contract";

        $res = $this->api_request("POST", "", $data);

        return response()->json([
            "data" => $res
        ]);
    }

    public function api_request($method,$path,$data = []){

        $url = "https://a2a-api.klipwallet.com/v2/a2a/prepare";

        $ch = curl_init($url);

        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = json_decode(curl_exec($ch),true);
//        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }


}
