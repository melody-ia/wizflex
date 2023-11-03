<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Mails\AmazonSes;

use App\Models\Nft;
use App\Models\Contact;
use App\Models\AuthAuthor;
use App\Models\ApplyAuthAuthor;
use App\Models\LogApplyAuthAuthor;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\EndAuction;
use App\Models\Purchase;
use App\Models\Profile;
use App\Models\CacheNft;

use BlockSDK;

trait SingleNetDashController {
	//경매 진행중인 NFT 전체 목록
	public function auctionNfts(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;
		
		$data = $this->netClient()->getAuctionNfts([
			'contract_address' => envDB('CONTRACT_ADDRESS'),
			'order_by'         => 'blocknumber',
			'order_direction'  => 'desc',
			'offset'           => $offset,
			'limit'            => $limit
		]);
		
		$payload = $data['payload'];
		
		$tokens = [];
		foreach($payload['auctions'] as $token){
			$end_date = date('Y-m-d',$token['end_time']);
			$token = $this->getNftData($token);
			if($token == false){
				continue;
			}
			
			$token['end_date'] = $end_date;
			
			if(empty($token['file_url']) == true && empty($token['image_url']) == false){
				$token['file_url'] = $token['image_url'];
			}else if(empty($token['file_url']) == true && empty($token['video_url']) == false){
				$token['file_url'] = $token['video_url'];
			}
			
			if(empty($token['creator_address'])){
				$token['creator_address'] = $token['creator_address'];
			}
			
			
			array_push($tokens,$token);
		}
		
		
		return response()->json([
			'data' => $tokens,
			'total' => $payload['total_auctions'],
		]);
	}
	
	//대시보드 통계 데이터
	public function get(Request $request){
		$year = date('Y');
		$month = date('m');
		
		if($month == 1){
			$prevYear = $year - 1;
			$prevMonth = 12;
		}else{
			$prevYear = $year;
			$prevMonth = $month - 1;
		}
		
		$lastMonthMint = DB::table('count_mint')->where('year',$year)->where('month',$month)->orderBy('id','desc')->groupBy('year','month')->select(DB::raw('sum(cun) as cun,year,month,day'))->first();
		$prevMonthMint = DB::table('count_mint')->where('year',$prevYear)->where('month',$prevMonth)->orderBy('id','desc')->groupBy('year','month')->select(DB::raw('sum(cun) as cun,year,month,day'))->first();
		$lastMonthMintCun = empty($lastMonthMint)?0:$lastMonthMint->cun;
		$prevMonthMintCun = empty($prevMonthMint)?0:$prevMonthMint->cun;

		
		$lastMonthCreateAuction = DB::table('count_create_auction')->where('year',$year)->where('month',$month)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,year,month,day'))->first();
		$prevMonthCreateAuction = DB::table('count_create_auction')->where('year',$prevYear)->where('month',$prevMonth)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,year,month,day'))->first();
		$lastMonthCreateAuctionCun = empty($lastMonthCreateAuction)?0:$lastMonthCreateAuction->cun;
		$prevMonthCreateAuctionCun = empty($prevMonthCreateAuction)?0:$prevMonthCreateAuction->cun;
		
		
		$lastMonthPurchase = DB::table('count_purchase')->where('year',$year)->where('month',$month)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->first();
		$prevMonthPurchase = DB::table('count_purchase')->where('year',$prevYear)->where('month',$prevMonth)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->first();
		$lastMonthPurchaseCun = empty($lastMonthPurchase)?0:$lastMonthPurchase->cun;
		$lastMonthPurchaseTotal = empty($lastMonthPurchase)?0:$lastMonthPurchase->total;
		$prevMonthPurchaseCun = empty($prevMonthPurchase)?0:$prevMonthPurchase->cun;
		$prevMonthPurchaseTotal = empty($prevMonthPurchase)?0:$prevMonthPurchase->total;
		
		$lastMonthEndAuction = DB::table('count_end_auction')->where('year',$year)->where('month',$month)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->first();
		$prevMonthEndAuction = DB::table('count_end_auction')->where('year',$prevYear)->where('month',$prevMonth)->groupBy('year','month')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->first();
		$lastMonthEndAuctionCun = empty($lastMonthEndAuction)?0:$lastMonthEndAuction->cun;
		$lastMonthEndAuctionTotal = empty($lastMonthEndAuction)?0:$lastMonthEndAuction->total;
		$prevMonthEndAuctionCun = empty($prevMonthEndAuction)?0:$prevMonthEndAuction->cun;
		$prevMonthEndAuctionTotal = empty($prevMonthEndAuction)?0:$prevMonthEndAuction->total;
		
		
		$purchase30 = DB::table('count_purchase')->orderBy('id','desc')->groupBy('year','month','day')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->limit(30)->get();
		$endAuction30 = DB::table('count_end_auction')->orderBy('id','desc')->groupBy('year','month','day')->select(DB::raw('sum(cun) as cun,sum(total) as total,year,month,day'))->limit(30)->get();
		

		$purchase30Days = [];
		foreach($purchase30 as $purchase){
			$purchaseTime = strtotime("{$purchase->year}-{$purchase->month}-{$purchase->day}");
			if($purchaseTime < time() - (29 * 86400)){
				continue;
			}
			
			$purchase30Days[$purchaseTime] = $purchase;
		}
		
		$endAuction30Days = [];
		foreach($endAuction30 as $endAuction){
			$endAuctionTime = strtotime("{$endAuction->year}-{$endAuction->month}-{$endAuction->day}");
			if($endAuctionTime < time() - (29 * 86400)){
				continue;
			}
			
			$endAuction30Days[$endAuctionTime] = $endAuction;
		}
		
		for($i=29;$i>=0;$i--){
			$time = time() - ($i * 86400);
			$time  = strtotime(date('Y-m-d',$time));
			
			if(empty($purchase30Days[$time]) == true){
				$purchase30Days[$time] = [
					'cun'   => 0,
					'total' => 0
				];
			}
			
			if(empty($endAuction30Days[$time]) == true){
				$endAuction30Days[$time] = [
					'cun'   => 0,
					'total' => 0
				];
			}
		}
		
		return response()->json([
			'data' => [
				'mint' => [
					'last' => [
						'cun' => $lastMonthMintCun
					],
					'prev' => [
						'cun' => $prevMonthMintCun
					]
				],
				'create_auction' => [
					'last' => [
						'cun' => $lastMonthCreateAuctionCun
					],
					'prev' => [
						'cun' => $prevMonthCreateAuctionCun
					]
				],				
				'purchase' => [
					'last' => [
						'cun' => $lastMonthPurchaseCun,
						'total' => round($lastMonthPurchaseTotal,6),
					],
					'prev' => [
						'cun' => $prevMonthPurchaseCun,
						'total' => round($prevMonthPurchaseTotal,6),
					]
				],			
				'end_auction' => [
					'last' => [
						'cun' => $lastMonthEndAuctionCun,
						'total' => round($lastMonthEndAuctionTotal,6),
					],
					'prev' => [
						'cun' => $prevMonthEndAuctionCun,
						'total' => round($prevMonthEndAuctionTotal,6),
					]
				],
				'purchase_30d' => $purchase30Days,
				'endAuction_30d' => $endAuction30Days,
			]
		]);
	}	

	//성사된 경매 거래 목록
	public function endAuction(Request $request){
		$order_key  = empty($request->order_key)?'created_at':$request->order_key;
		$order_sort = empty($request->order_sort)?'desc':$request->order_sort;
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;
		
		$endAuction = EndAuction::orderBy($order_key,$order_sort);
		$endAuctions = $endAuction->get();
		
		$totalAmount = 0;
		foreach($endAuctions as $endAuction){
			$transfer =  $endAuction->transfer();
			$totalAmount += $transfer['value'];
		}
		
		$endAuctions = $endAuction->offset($offset)->limit($limit)->get();
		
		$data = [];
		foreach($endAuctions as $endAuction){
			$nft = Nft::find($endAuction->nft_id);
			$nft->file_url = $nft->file();
			$nft->transfer =  $endAuction->transfer();
			array_push($data,$nft);
		}
		
		$total = EndAuction::count();
		return response()->json([
			'totalAmount' => $totalAmount,
			'total' => $total,
			'data' => $data
		]);
	}	
	
	//성사된 경매 거래 검색
	public function searchEndAuction(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;
		
		$endAuction = EndAuction::whereBetween('created_at',[$request->start_date,$request->end_date])->orderBy('created_at','desc');
		$endAuctions = $endAuction->get();
		
		$totalAmount = 0;
		foreach($endAuctions as $endAuctionData){
			$transfer =  $endAuctionData->transfer();
			$totalAmount += $transfer['value'];
		}
		
		$endAuctions = $endAuction->offset($offset)->limit($limit)->get();
		
		$data = [];
		foreach($endAuctions as $endAuctionData){
			$nft = Nft::find($endAuctionData->nft_id);
			$nft->file_url = $nft->file();
			$nft->transfer =  $endAuctionData->transfer();
			array_push($data,$nft);
		}
		
		$totalCun = $endAuction->count();
		return response()->json([
			'totalAmount' => $totalAmount,
			'total' => $totalCun,
			'data' => $data
		]);
	}
	
	//일반 판매 목록
	public function purchase(Request $request){
		$order_key  = empty($request->order_key)?'created_at':$request->order_key;
		$order_sort = empty($request->order_sort)?'desc':$request->order_sort;
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;
		
		$purchase = Purchase::orderBy($order_key,$order_sort);
		$purchases = $purchase->get();
		
		$totalAmount = 0;
		foreach($purchases as $purchaseData){
			$transfer =  $purchaseData->transfer();
			$totalAmount += $transfer['value'];
		}
		
		$purchases = $purchase->offset($offset)->limit($limit)->get();
		
		$data = [];
		foreach($purchases as $purchaseData){
			$nft = Nft::find($purchaseData->nft_id);
			$nft->file_url = $nft->file();
			$nft->transfer =  $purchaseData->transfer();
			array_push($data,$nft);
		}
		
		$total = Purchase::count();
		return response()->json([
			'totalAmount' => $totalAmount,
			'total' => $total,
			'data' => $data
		]);
	}
	
	//일반 판매 검색
	public function searchPurchase(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;
		
		$purchase = Purchase::whereBetween('created_at',[$request->start_date,$request->end_date])->orderBy('created_at','desc');
		$purchases = $purchase->get();
		
		$totalAmount = 0;
		foreach($purchases as $purchaseData){
			$transfer =  $purchaseData->transfer();
			$totalAmount += $transfer['value'];
		}
		
		$purchases = $purchase->offset($offset)->limit($limit)->get();
		
		$data = [];
		foreach($purchases as $purchaseData){
			$nft = Nft::find($purchaseData->nft_id);
			$nft->file_url = $nft->file();
			$nft->transfer =  $purchaseData->transfer();
			array_push($data,$nft);
		}
		
		$totalCun = $purchase->count();
		return response()->json([
			'totalAmount' => $totalAmount,
			'total' => $totalCun,
			'data' => $data
		]);
	}
}