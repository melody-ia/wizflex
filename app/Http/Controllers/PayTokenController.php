<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Models\PayToken;


class PayTokenController extends Controller
{
	public function list(Request $request){
		$tokens = PayToken::get();
	
		return response()->json([
			'data' => $tokens,
			'total'=> PayToken::count()
		]);
	}
}