<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\User;

class AdminController extends Controller
{
	public function authCheck(Request $request){
		if(Auth::check() == true){
			return response()->json([
				"data" => true
			]);
		}
		
		return response()->json([
			"data" => false
		]);
	}
	
	public function login(Request $request){
		if(empty($request->email) == true){
			return response()->json([
				"error" => [
					"message" => "이메일을 입력해주세요"
				]
			]);
		}else if(empty($request->password) == true){
			return response()->json([
				"error" => [
					"message" => "비밀번호를 입력해주세요"
				]
			]);
		}
		
		$user = User::where('email',$request->email)->first();
		if(empty($user) || Hash::check($request->password, $user->password) == false || $user->isAdmin() == false){
			return response()->json([
				"error" => [
					"asd" => $user,
					"message" => "인증에 실패하였 습니다"
				]
			]);
		}
		
		Auth::login($user);
		return response()->json([
			'data' => $user
		]);
	}	
	
	public function webLogout(Request $request){
		if(Auth::check() == false){
			return response()->json([
				"error" => [
					"message" => "로그인 상태가 아닙니다"
				]
			]);
		}
		
		Auth::logout();
		
		return redirect('/admin/login'); 
	}	
	
	public function logout(Request $request){
		if(Auth::check() == false){
			return response()->json([
				"error" => [
					"message" => "로그인 상태가 아닙니다"
				]
			]);
		}
		
		Auth::logout();
		
		return response()->json([
			'data' => true
		]);
	}
	
	public function me(Request $request){
		return response()->json([
			'data' => Auth::user()
		]);
	}
}