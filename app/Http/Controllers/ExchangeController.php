<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ExchangeController extends Controller
{
    public function __construct()
    {
        $this->baseURL1 = "https://api.bithumb.com/public";
        $this->baseURL2 = "https://api.binance.com/api/v3/avgPrice?symbol=";//BNBUSDT
    }

    public function api_request($method,$path, $type, $data = []){
        if ($type == "KRW"){
            $url = $this->baseURL1 . $path;
        }else {
            $url = $this->baseURL2 . $path;
        }

        if($method == "GET" && count($data) > 0){
            $url = $url . "?";
            foreach($data as $key => $value){
                if($value === true){
                    $url = $url . $key . "=true&";
                }else if($value === false){
                    $url = $url . $key . "=false&";
                }else{
                    $url = $url . $key . "=" . $value . "&";
                }
            }
        }

        $ch = curl_init($url);

        if($method == "POST" || $method == "DELETE"){
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = json_decode(curl_exec($ch),true);
        curl_close($ch);


//		return $result['data'];
        return $result;
    }

    public function getPrice(Request $request){

        if (empty($request->currency) == false && Str::lower($request->currency) == "usd"){
            $klayResult = $this->api_request("GET","KLAYUSDT", "USD")['price'];
            $bscResult = $this->api_request("GET","BNBUSDT", "USD")['price'];
            $ethResult = $this->api_request("GET","ETHUSDT", "USD")['price'];
            $maticResult = $this->api_request("GET","MATICUSDT", "USD")['price'];
        }else {
            $klayResult = $this->api_request("GET","/ticker/KLAY_KRW", "KRW")['data']['closing_price'];
            $bscResult = $this->api_request("GET","/ticker/BNB_KRW", "KRW")['data']['closing_price'];
            $ethResult = $this->api_request("GET","/ticker/ETH_KRW", "KRW")['data']['closing_price'];
            $maticResult = $this->api_request("GET","/ticker/MATIC_KRW", "KRW")['data']['closing_price'];
        }

        return response()->json([
            'klay' => $klayResult,
            'bsc' => $bscResult,
            'bnb' => $bscResult,
            'eth' => $ethResult,
            'matic' => $maticResult,
        ]);
    }
}
