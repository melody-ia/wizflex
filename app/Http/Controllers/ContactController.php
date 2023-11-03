<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Models\Contact;

class ContactController extends Controller
{
	
	public function verify($request){
		if(empty($request->name) == true){
			return response()->json([
				"error" => [
					"message" => "이름을 입력해주세요"
				]
			]);
		}else if(empty($request->email) == true){
			return response()->json([
				"error" => [
					"message" => "이메일을 입력해주세요"
				]
			]);
		}else if(empty($request->subject) == true){
			return response()->json([
				"error" => [
					"message" => "제목을 입력해주세요"
				]
			]);
		}else if(empty($request->message) == true){
			return response()->json([
				"error" => [
					"message" => "내용을 입력해주세요"
				]
			]);
		}else if(preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $request->email) == false){
			return response()->json([
				"error" => [
					"message" => "잘못된 이메일 입니다"
				]
			]);
		}else if(mb_strlen($request->name,'utf8') > 20){
			return response()->json([
				"error" => [
					"message" => "이름은 20자를 초과할수 없습니다"
				]
			]);
		}else if(mb_strlen($request->email,'utf8') > 50){
			return response()->json([
				"error" => [
					"message" => "이메일은 50자를 초과할수 없습니다"
				]
			]);
		}else if(mb_strlen($request->subject,'utf8') > 60){
			return response()->json([
				"error" => [
					"message" => "제목은 60자를 초과할수 없습니다"
				]
			]);
		}else if(mb_strlen($request->message,'utf8') > 2000){
			return response()->json([
				"error" => [
					"message" => "내용은 2000자를 초과할수 없습니다"
				]
			]);
		}
		
		return false;
	}
	
	public function created(Request $request){
		$result = $this->verify($request);
		if($result != false){
			return $result;
		}
		
		
		$contact = new Contact();
		$contact->name = $request->name;
		$contact->email = $request->email;
		$contact->subject = $request->subject;
		$contact->message = $request->message;
		$contact->save();
		
		return response()->json([
			"created" => true
		]);
	}
}
