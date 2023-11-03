<?php

namespace App\Http\Middleware;

use Closure;

use Ethereum\EcRecover;
use Ethereum\KlayRecover;

use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    public function handle($request, Closure $next)
    {
		if(Auth::check() == false){
			return response()->json([
				"error" => [
					"unauth" => true,
					"message" => "로그인 바랍니다"
				]
			],200);
		}
		
		$user = Auth::user();
		if($user->isAdmin() == false){
			return response()->json([
				"error" => [
					"unauth" => true,
					"message" => "관리자 권한이 없는 사용자"
				]
			],200);
		}
		
		
        return $next($request);
    }
}
