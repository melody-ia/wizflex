<?php
use App\Models\FrontEnv;
use App\Models\CategoryEnv;

function query($sql){
	$mysqlConnect = mysqli_connect(env('DB_HOST'),env('DB_USERNAME'),env('DB_PASSWORD'),env('DB_DATABASE'));
	$query = mysqli_query($mysqlConnect, $sql);
	
	$results = [];
	while($row = mysqli_fetch_array($query)){
		array_push($results,$row);
	}
	
	return $results;
}

function envDB($key,$value=null){
	
	$mysqlConnect = mysqli_connect(env('DB_HOST'),env('DB_USERNAME'),env('DB_PASSWORD'),env('DB_DATABASE'));
	$key = str_replace('#','',mysqli_real_escape_string($mysqlConnect,$key));
	if(empty($value) == false){
		//value 가 있을경우 INSERT 또는 UPDATE 실행
		
		$value = str_replace('#','',mysqli_real_escape_string($mysqlConnect,$value));
		$sql = "INSERT INTO laravel_env VALUES('{$key}','{$value}') ON DUPLICATE KEY UPDATE value='{$value}'";
		return $value;
	}
	
	$sql = "SELECT * FROM laravel_env WHERE name='{$key}' LIMIT 0,1";
	$query = mysqli_query($mysqlConnect, $sql);
	
	if(empty($value) == true && (empty($query->num_rows) == true || $query->num_rows == 0)){
		return false;
	}
	
	$row = mysqli_fetch_array($query);
	if(empty($value) == true){
		return $row['value'];
	}
	
	return true;
}

function frontEnvDB($key,$value=null){
	$mysqlConnect = mysqli_connect(env('DB_HOST'),env('DB_USERNAME'),env('DB_PASSWORD'),env('DB_DATABASE'));
	
	$key = str_replace('#','',mysqli_real_escape_string($mysqlConnect,$key));
	if(empty($value) == false){
		//value 가 있을경우 INSERT 또는 UPDATE 실행
		
		$value = str_replace('#','',mysqli_real_escape_string($mysqlConnect,$value));
		$sql = "INSERT INTO front_env VALUES('{$key}','{$value}') ON DUPLICATE KEY UPDATE value='{$value}'";
		return $value;
	}
	
	$sql = "SELECT * FROM front_env WHERE name='{$key}' LIMIT 0,1";
	$query = mysqli_query($mysqlConnect, $sql);
	
	if(empty($value) == true && (empty($query->num_rows) == true || $query->num_rows == 0)){
		return false;
	}
	
	$row = mysqli_fetch_array($query);
	if(empty($value) == true){
		return $row['value'];
	}
	
	return true;
}

function analytics(){
	
	$env = FrontEnv::find('ANALYTICS');
	if(empty($env) == true){
		return "";
	}
	
	
	return $env->value;
}

function frontEnvJson(){
	
	$envs = FrontEnv::get();
	
	$json_data = [];
	foreach($envs as $env){
		$json_data[$env['name']] = $env['value'];
	}
	
	$categoryEnvs = CategoryEnv::get();
	$json_data['VUE_APP_CATEGORY'] = $categoryEnvs;
	
	$json_data['VUE_APP_UPLOAD_FILTER_LAYER'] = envDB('UPLOAD_FILTER_LAYER');
	
	$sql = "
		SELECT 
		layer_value.*,
		layer_type.priority as priority
		FROM layer_value
		JOIN layer_type on layer_type.name=layer_value.type
	";
	$json_data['VUE_APP_LAYERS'] = query($sql);
	
	return json_encode($json_data);
}