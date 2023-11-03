<?php

namespace App\Http\Middleware;

use Closure;

use Ethereum\EcRecover;
use Ethereum\KlayRecover;

use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    public function handle($request, Closure $next)
    {
		
		$user = Auth::user();
		if($user->isSuperAdmin() == false){
			return response()->json([
				"error" => [
					"message" => "슈퍼 권한이 없는 사용자"
				]
			],200);
		}
		
		
        return $next($request);
    }
}
