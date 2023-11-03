<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use BlockSDK;
class SolcController extends Controller
{
	
	public function netClient(){
		if(envDB('BASE_MAINNET') == 'ETH')
			return BlockSDK::createEthereum(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'BSC')
			return BlockSDK::createBinanceSmart(envDB('BLOCKSDK_TOKEN'));
		if(envDB('BASE_MAINNET') == 'KLAY')
			return BlockSDK::createKlaytn(envDB('BLOCKSDK_TOKEN'));
	}
	
	public function __construct(){
		$this->solcClient = BlockSDK::createSolc(envDB("BLOCKSDK_TOKEN"));
	}
	
	
	public function encodefunction(Request $request){
		$result = $this->solcClient->encodefunction([
			'method' => $request->method,
			'parameter_type' => $request->parameter_type,
			'parameter_data' => $request->parameter_data
		]);
		
		return response()->json($result);
	}
	
	public function paused(Request $request){
		$data = $this->netClient()->getContractRead([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'method' => 'paused',
			'return_type' => 'bool',
			'parameter_type' => [],
			'parameter_data' => []
		]);
		
		if(empty($data['payload']['result']) == true){
			return response()->json([
				'data' => false
			]);
		}
		
		return response()->json([
			'data' => $data['payload']['result']
		]);
	}
}
