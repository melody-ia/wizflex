<?php

namespace App\Http\Middleware;


use Illuminate\Support\Facades\Auth;

use Closure;

use Ethereum\EcRecover;
use Ethereum\KlayRecover;

use App\Models\Profile;
use Illuminate\Support\Str;

class AuthCheck
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        if ($this->get_client_ip() == "61.74.109.114"){
            if (empty($request->auth_walletName) == true){
                return response()->json([
                    "error" => [
                        "message" => "지갑 이름이 누락되었습니다"
                    ]
                ],200);
            }

            if($request->auth_walleteName != "KLIP"){
                $this->commonValidate($request);

            }else if($request->auth_walletName == "KLIP"){
                $this->klipResult($request);

            }
        }else {

            $this->commonValidate($request);

        }

		$request->auth_address = strtolower($request->auth_address);
		
		$profile = Profile::where('address',$request->auth_address)->first();
		if(empty($profile) == true){
			$profile = new Profile();
			$profile->address = $request->auth_address;
			$profile->save();
		}
		
        return $next($request);
    }


    function commonValidate($request){
        if(empty($request->auth_signature) == true){
            return response()->json([
                "error" => [
                    "message" => "서명 메세지가 누락되었습니다"
                ]
            ],200);
        }else if(empty($request->auth_timestamp) == true){
            return response()->json([
                "error" => [
                    "message" => "서명 메세지 인증 시간이 누락되었습니다"
                ]
            ],200);
        }else if(empty($request->auth_address) == true){
            return response()->json([
                "error" => [
                    "message" => "인증에 사용된 주소가 누락되었습니다"
                ]
            ],200);
        }

        $message = "timestamp:" . $request->auth_timestamp;


        $metamaskRecoveredAddress = EcRecover::personalEcRecover($message, $request->auth_signature);
        $kaikasRecoveredAddress   = KlayRecover::personalEcRecover($message, $request->auth_signature);


        if($metamaskRecoveredAddress != $request->auth_address && $kaikasRecoveredAddress != $request->auth_address){
            return response()->json([
                "error" => [
                    "message" => "인증 실패"
                ]
            ],401);
        }else if(($request->auth_timestamp + 300) < time()){
            return response()->json([
                "error" => [
                    "message" => "인증 시간을 초과하였습니다"
                ]
            ],401);
        }
    }


    function klipResult($request){
        if (empty($request->auth_klip_request_key) == true){
            return response()->json([
                "error" => [
                    "message" => "KLIP 서명이 누락되었습니다"
                ]
            ],200);
        }

        // 클립 재인증
        $result = $this->request("GET", $request->auth_klip_request_key);


        if (empty($result['result']) == true){
            return response()->json([
                "error" => [
                    "message" => "KLIP 인증에 실패하셨습니다."
                ]
            ],200);
        }



        if (Str::lower($result["result"]["klaytn_address"]) != Str::lower($request->auth_address)){
            return response()->json([
                "error" => [
                    "message" => "KLIP 인증계좌가 동일하지 않습니다."
                ]
            ],200);
        }

    }

    function request($method, $request_key){
        $url = "https://a2a-api.klipwallet.com/v2/a2a/result?request_key=" . $request_key;

        $ch = curl_init($url);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

		$result = json_decode(curl_exec($ch),true);

		curl_close($ch);


		return $result;
    }

    public function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}
