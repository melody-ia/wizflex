<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\LayerType;
use App\Models\LayerValue;


use Image;

class LayerController extends Controller
{
	public function AddType(Request $request){
		if(empty($request->name) == true){
			return response()->json([
				"error" => [
					"message" => "NAME 가 누락되었습니다"
				]
			]);
		}else if(empty($request->priority) == true){
			return response()->json([
				"error" => [
					"message" => "PRIORITY 가 누락되었습니다"
				]
			]);
		}
		
		
		$type = LayerType::where('name',$request->name)->first();
		if(empty($type) == false){
			return response()->json([
				"error" => [
					"message" => "이미 존재하는 레이어 타입 입니다"
				]
			]);
		}
		
		$type = new LayerType();
		$type->priority = $request->priority;
		$type->name = $request->name;
		$type->save();
		
		return response()->json([
			'data' => true,
		]);
	}
	
	public function AddValue(Request $request){
		if(empty($request->type) == true){
			return response()->json([
				"error" => [
					"message" => "TYPE 가 누락되었습니다"
				]
			]);
		}else if(empty($request->value) == true){
			return response()->json([
				"error" => [
					"message" => "VALUE 가 누락되었습니다"
				]
			]);
		}else if(empty($request->image) == true){
			return response()->json([
				"error" => [
					"message" => "VALUE 가 누락되었습니다"
				]
			]);
		}
		
		$value = LayerValue::where('type',$request->type)->where('value',$request->value)->first();
		if(empty($value) == false){
			return response()->json([
				"error" => [
					"message" => "이미 존재하는 TYPE-VALUE쌍 입니다"
				]
			]);
		}
		
		$type = LayerType::where('name',$request->type)->first();
		if(empty($type) == true){
			return response()->json([
				"error" => [
					"message" => "존재하지 않는 레이어 타입 입니다"
				]
			]);
		}
		
		$extension = strtolower($request->image->extension());
		$filename = Str::random(30) . "." . $extension;
		$path = $request->image->storeAs('layer-images',$filename,'public');
		if($path == false){
			return response()->json([
				'error' => [
					'message' => "파일 저장에 실패하였 습니다"
				]
			]);
		}

		//$path = storage_path('app/public/'.$path);
			
		$value = new LayerValue();
		$value->type = $request->type;
		$value->value = $request->value;
		$value->image = $path;
		$value->save();
		
		return response()->json([
			'data' => true,
		]);
	}
	
	public function RemoveType(Request $request){
		if(empty($request->name) == true){
			return response()->json([
				"error" => [
					"message" => "NAME 가 누락되었습니다"
				]
			]);
		}

		$type = LayerType::where('name',$request->name)->first();
		if(empty($type) == true){
			return response()->json([
				"error" => [
					"message" => "존재하지 않는 LayerType 입니다"
				]
			]);
		}
		
		$type->delete();
		
		return response()->json([
			'data' => true,
		]);
	}
	public function RemoveValue(Request $request){
		if(empty($request->type) == true){
			return response()->json([
				"error" => [
					"message" => "TYPE 가 누락되었습니다"
				]
			]);
		}else if(empty($request->value) == true){
			return response()->json([
				"error" => [
					"message" => "VALUE 가 누락되었습니다"
				]
			]);
		}

		$value = LayerValue::where('type',$request->type)->where('value',$request->value)->first();
		if(empty($value) == true){
			return response()->json([
				"error" => [
					"message" => "존재하지 않는 VALUE 입니다"
				]
			]);
		}
		
		$value->delete();
		
		return response()->json([
			'data' => true,
		]);
	}
	
	public function GetType(Request $request){
		
		$layers = LayerType::all();
		
		return response()->json([
			'data' => $layers,
			'total' => LayerType::count(),
		]);
	}
	
	public function GetLayerValues(Request $request){
		
		$layers = LayerValue::all();
		
		$result = [];
		foreach($layers as $layer){
			
			array_push($result,[
				'type' => $layer->type,
				'value' => $layer->value,
				'image' => envDB('BASE_IMAGE_URI') . '/storage/' . $layer->image,
			]);
			
		}
		
		return response()->json([
			'data' => $result,
			'total' => LayerValue::count(),
		]);
	}
	
	public function GenImage(Request $request){
		if(empty($request->layers) == true){
			return response()->json([
				"error" => [
					"message" => "layers 가 누락되었습니다"
				]
			]);
		}else if(gettype($request->layers) == 'Array'){
			return response()->json([
				"error" => [
					"message" => "layers의 타입은 Array 형태 여야 합니다"
				]
			]);
		}
		
		$layers = [];
		foreach($request->layers as $layer){;
			$layer = json_decode($layer,true);

			$layerType = LayerType::where('name',$layer['type'])->first();
			if(empty($layerType) == true){
				return response()->json([
					"error" => [
						"message" => $layer['type'] . "은 존재하지 않는 layer TYPE 입니다."
					]
				]);
			}
			
			$layerValue = LayerValue::where('type',$layer['type'])->where('value',$layer['value'])->first();
			if(empty($layerValue) == true){
				return response()->json([
					"error" => [
						"message" => $layer['value'] . "은 존재하지 않는 layer VALUE 입니다."
					]
				]);
			}
			
			$layers[$layerType->priority] = $layerValue;
		}
		
		$filename = "";
		ksort($layers);
		foreach($layers as $priority => $layer){
			$filename .= $layer->type . $layer->value;
			if(empty($layerImage) == true){
				$layerImage = Image::make(storage_path('app/public/' . $layer->image));
				continue;
			}
			
			$layerImage->insert(storage_path('app/public/' . $layer->image));
		}
		

		$filename = md5($filename) . ".png";
		
		$layerImage->save(storage_path('app/public/layer-images/' . $filename));
		
		return response()->json([
			'image_url' => envDB('BASE_IMAGE_URI') . '/storage/layer-images/' . $filename,
		]);
	}
}