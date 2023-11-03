<?php

namespace App\Http\Middleware;

use Closure;

use Ethereum\EcRecover;
use Ethereum\KlayRecover;

use Illuminate\Support\Facades\Auth;

class DemoLock
{
    public function handle($request, Closure $next)
    {
		if(empty(env('APP_DEMO')) == false){
			return response()->json([
				"error" => [
					"message" => "데모 페이지에서는 수정 불가능합니다"
				]
			],200);
		}
    }
}
