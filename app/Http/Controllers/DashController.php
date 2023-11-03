<?php

namespace App\Http\Controllers;

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


if(envDB('HAS_MULTI_NET') == 1){
	//다중 메인넷
	class NetDashController extends Controller {
		use Modules\MultiNetNftController;
		use Modules\MultiNetDashController;
	}
}else{
	//싱글 메인넷
	class NetDashController extends Controller {
		use Modules\SingleNetNftController;
		use Modules\SingleNetDashController;
	}
}

class DashController extends NetDashController
{
	public function getCache($id){
		$cache = CacheNft::find($id);
		if(empty($cache) == true){
			return false;
		}else if((strtotime($cache->updated_at) + envDB('CACHE_TIME_NFT')) < time() ){
			return false;//지정된 캐시시간보다 길어졋을경우
		}
		return CacheNft::find($id);
	}
	public function cacheSave($id,$hex){
		$cacheNft = CacheNft::find($id);
		if(empty($cacheNft) == true){
			$cacheNft = new CacheNft();
		}

		$cacheNft->id = $id;
		$cacheNft->data = $hex;
		$cacheNft->save();
	}
	public function hexToBool($hex){
		return (bool)hexdec(substr($hex,0,64));
	}
	public function hexToDec($hex){
		return hexdec(substr($hex,0,64));
	}
	public function hexToOffer($hex){
		$isForSale = (bool)hexdec(substr($hex,0,64));
		$seller = substr($hex,64,64);
		$minValue = hexdec(substr($hex,128,64)) / 1000000000000000000;
		$endTime = hexdec(substr($hex,192,64));

		return [
			'isForSale' => $isForSale,
			'seller' => $seller,
			'minValue' => $minValue,
			'endTime' => $endTime,
		];
	}
	public function getProfile($address){
		$profile = Profile::where("address",$address)->first();
		if(empty($profile) == true){
			$profile = [
				'address' => $address,
				'avatar'  => envDB('BASE_IMAGE_URI') . '/img/profile.png',
				'auth' => 0
			];
		}else{
			$profile = [
				'address' => $address,
				'avatar'  => $profile->avatar(),
				'name'    => $profile->name,
				'nick'    => $profile->nick,
				'auth'    => $profile->auth
			];
		}

		if(empty($profile['name']) == true){
			$profile['name'] = $address;
		}
		if(empty($profile['nick']) == true){
			$profile['nick'] = $address;
		}

		return $profile;
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

	//NFT 전체 목록
	public function nfts(Request $request){
		$order_key  = empty($request->order_key)?'created_at':$request->order_key;
		$order_sort = empty($request->order_sort)?'desc':$request->order_sort;
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$nfts = Nft::orderBy($order_key,$order_sort)->offset($offset)->limit($limit)->get();
		foreach($nfts as $key => $nft){
			$nft->file_url = $nft->file();
            if(substr($nft->file_name,-4) == '.mp3'){
                if(empty(envDB('IS_AWS_S3')) == true){
                    $nfts[$key]['file_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->thumbnail);
                }else if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/nft_files/' . $nft->thumbnail)) == true ){
                    $nfts[$key]['file_url'] = envDB('BASE_IMAGE_URI') . Storage::url('nft_files/' . $nft->thumbnail);
                }else if(empty(envDB('IS_AWS_S3')) == false){
                    $nfts[$key]['file_url'] = envDB('BASE_AWS_S3_URI') . '/nft-files/' . $nft->thumbnail;
                }
            }
			$nfts[$key] = $nft;
		}

		$total = Nft::count();
		return response()->json([
			'total' => $total,
			'data' => $nfts
		]);
	}

	//NFT 검색
	public function searchNfts(Request $request){
		$where_key  = empty($request->where_key)?'name':$request->where_key;
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$nfts = Nft::where($request->where_key,'like','%'.$request->where_value.'%')->orderBy('created_at','desc');

		$data = $nfts->offset($offset)->limit($limit)->get();
		foreach($data as $key => $nft){
			$nft->file_url = $nft->file();
			$data[$key] = $nft;
		}

		$total = $nfts->count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//NFT 삭제
	public function deleteNft(Request $request){
		$nft = Nft::find($request->nft_id);
		if(empty($nft) == true){
			return response()->json([
				'error' => [
					'message' => '존재하지 않는 NFT 입니다'
				],
			]);
		}

		$nft->delete();
		return response()->json([
			'data' => true
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
			$nft->file_url = $nft->file(); // ????
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

	//인증 처리 로그 목록
	public function authAuthorLogs(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$applyAuthAuthorLogs = LogApplyAuthAuthor::orderBy('id','desc')->offset($offset)->limit($limit)->get();
		$data = [];
		foreach($applyAuthAuthorLogs as $applyAuthAuthorLog){
			$applyAuthAuthorLog->profile = $this->getProfile($applyAuthAuthorLog->address);
			array_push($data,$applyAuthAuthorLog);
		}

		$total = LogApplyAuthAuthor::count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//인증 처리 로그 검색
	public function searchAuthAuthorLogs(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$applyAuthAuthorLog = LogApplyAuthAuthor::where($request->where_key,'like','%'.$request->where_value.'%');
		$applyAuthAuthorLogs = $applyAuthAuthorLog->offset($offset)->limit($limit)->get();

		$data = [];
		foreach($applyAuthAuthorLogs as $applyAuthAuthorLogData){
			$applyAuthAuthorLogData->profile = $this->getProfile($applyAuthAuthorLogData->address);
			array_push($data,$applyAuthAuthorLogData);
		}

		$total = $applyAuthAuthorLog->count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//인증된 저자 목록
	public function authAuthors(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$authAuthors = AuthAuthor::orderBy('id','desc')->offset($offset)->limit($limit)->get();
		$data = [];
		foreach($authAuthors as $authAuthor){
			$authAuthor->profile = $this->getProfile($authAuthor->address);
			array_push($data,$authAuthor);
		}

		$total = AuthAuthor::count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//인증된 저자 검색
	public function searchAuthAuthors(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$authAuthor = AuthAuthor::where($request->where_key,'like','%'.$request->where_value.'%');
		$authAuthors = $authAuthor->offset($offset)->limit($limit)->get();

		$data = [];
		foreach($authAuthors as $authAuthorData){
			$authAuthorData->profile = $this->getProfile($authAuthorData->address);
			array_push($data,$authAuthorData);
		}

		$total = $authAuthor->count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//인증된 신청 저자 목록
	public function applyAuthAuthors(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$applyAuthAuthors = ApplyAuthAuthor::orderBy('id','desc')->offset($offset)->limit($limit)->get();

		$data = [];
		foreach($applyAuthAuthors as $applyAuthAuthor){
			$applyAuthAuthor->profile = $this->getProfile($applyAuthAuthor->address);
			array_push($data,$applyAuthAuthor);
		}

		$total = ApplyAuthAuthor::count();
		return response()->json([
			'total' => $total,
			'data' => $applyAuthAuthors
		]);
	}

	//인증된 저자 수동 추가
	public function addAuthAuthors(Request $request){
		$hasAuthAuthor = AuthAuthor::where('address',$request->address)->count();
		if(empty($hasAuthAuthor) == false){
			return response()->json([
				'error' => [
					'message' => '이미 인증된 저자 입니다'
				],
			]);
		}

		$authAuthor = new AuthAuthor();
		$authAuthor->address = $request->address;
		$authAuthor->phon = $request->phon;
		$authAuthor->email = $request->email;
		$authAuthor->save();

		$logApplyAuthAuthor = new LogApplyAuthAuthor();
		$logApplyAuthAuthor->address = $request->address;
		$logApplyAuthAuthor->name = $request->name;
		$logApplyAuthAuthor->phon = $request->phon;
		$logApplyAuthAuthor->email = $request->email;
		$logApplyAuthAuthor->description = $request->description;
		$logApplyAuthAuthor->auth = 1;
		$logApplyAuthAuthor->memo = $request->message;
		$logApplyAuthAuthor->save();


		$profile = Profile::firstOrCreate([
			'address' => $request->address,
		]);

		$profile->auth = 1;
		$profile->save();

		return response()->json([
			'data' => true
		]);
	}

	//인증된 신청 승인,거부
	public function processAuthAuthors(Request $request){
		$logApplyAuthAuthor = new LogApplyAuthAuthor();
		$profile = Profile::firstOrCreate([
			'address' => $request->address,
		]);

		$applyAuthAuthor = ApplyAuthAuthor::where('address',$request->address)->first();
		if($request->type == 'auth'){
			$subject = "인증 신청이 승인되었습니다";
			$email   = $applyAuthAuthor->email;

			$authAuthor = new AuthAuthor();
			$authAuthor->address = $request->address;
			$authAuthor->name = $applyAuthAuthor->name;
			$authAuthor->phon = $applyAuthAuthor->phon;
			$authAuthor->email = $applyAuthAuthor->email;
			$authAuthor->save();
			$logApplyAuthAuthor->auth = 1;
			$profile->auth = 1;
		}else{
			$authAuthor = AuthAuthor::where('address',$request->address)->first();
			$subject = "인증 신청이 거부되었습니다";


			if(empty($authAuthor) == false){
				$email   = $authAuthor->email;
				$authAuthor->delete();
			}else if(empty($applyAuthAuthor) == false){
				$email   = $applyAuthAuthor->email;
			}
			$logApplyAuthAuthor->auth = 0;
			$profile->auth = 0;

		}

		if(empty($applyAuthAuthor) == false){
			$logApplyAuthAuthor->address = $applyAuthAuthor->address;
			$logApplyAuthAuthor->name = $applyAuthAuthor->name;
			$logApplyAuthAuthor->phon = $applyAuthAuthor->phon;
			$logApplyAuthAuthor->email = $applyAuthAuthor->email;
			$logApplyAuthAuthor->description = $applyAuthAuthor->description;
			$logApplyAuthAuthor->memo = $request->message;
			$logApplyAuthAuthor->save();
			$applyAuthAuthor->delete();
		}

		$profile->save();

		$taget = [[
			'email' => $email
		]];

		Mail::to($taget)->send(
			new AmazonSes(
				[
					'subject' => $subject,
					'content' => $request->message
				]
			)
		);

		return response()->json([
			'data' => true
		]);
	}

	//관리자 목록
	public function users(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$userPrivileges = UserPrivilege::orderBy('id','desc')->offset($offset)->limit($limit)->get();

		$data = [];
		foreach($userPrivileges as $userPrivilege){
			$user = $userPrivilege->user();
			$user->authority = $userPrivilege->authority;
			array_push($data,$user);
		}

		$total = UserPrivilege::count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//관리자 갱신
	public function updateUser(Request $request){
		$user = User::find($request->id);
		if(empty($user) == true){
			return response()->json([
				'error' => [
					'message' => '존재하지 않는 유저 입니다'
				],
			]);
		}

		$user->email = $request->email;
		if(empty($request->password) == false){
			$user->password = Hash::make($request->password);
		}
		$user->name = $request->name;
		$user->phon = $request->phone;
		$user->save();

		return response()->json([
			'data' => $user,
			'update' => true
		]);
	}

	//관리자 추가
	public function createUser(Request $request){
		if(empty($request->password) == true){
			return response()->json([
				'error' => [
					'message' => '비밀번호를 입력해 주세요'
				],
			]);
		}

		$user = new User();
		$user->email = $request->email;
		$user->password = Hash::make($request->password);
		$user->name = $request->name;
		$user->phon = $request->phone;
		$user->save();

		$userPrivileges = new UserPrivilege();
		$userPrivileges->user_id = $user->id;
		$userPrivileges->authority = 'admin';
		$userPrivileges->save();

		return response()->json([
			'data' => $user,
			'update' => false
		]);
	}

	//관리자 제거
	public function deleteUser(Request $request){
		$user = User::find($request->user_id);
		if(empty($user) == true){
			return response()->json([
				'error' => [
					'message' => '존재하지 않는 유저 입니다'
				],
			]);
		}
		if($user->isSuperAdmin() == true){
			return response()->json([
				'error' => [
					'message' => '슈퍼 관리자 계정은 삭제할수 없습니다'
				],
			]);
		}
		$user->delete();
		return response()->json([
			'data' => true
		]);
	}

	//문의 목록
	public function contacts(Request $request){
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$contact = Contact::orderBy('id','desc')->offset($offset)->limit($limit)->get();

		$total = Contact::count();
		return response()->json([
			'total' => $total,
			'data' => $contact
		]);
	}

	//문의 검색
	public function searchContacts(Request $request){
		$where_key  = empty($request->where_key)?'name':$request->where_key;
		$offset = empty($request->offset)?0:$request->offset;
		$limit = empty($request->limit)?10:$request->limit;

		$contacts = Contact::where($request->where_key,'like','%'.$request->where_value.'%')->orderBy('created_at','desc');

		$data = $contacts->offset($offset)->limit($limit)->get();
		foreach($data as $key => $contact){
			$data[$key] = $contact;
		}

		$total = $contacts->count();
		return response()->json([
			'total' => $total,
			'data' => $data
		]);
	}

	//문의 답변
	public function contactsReply(Request $request){
		if(empty($request->id) == true){
			return response()->json([
				'error' => [
					'message' => '문의 ID 를 입력해주세요'
				],
			]);
		}else if(empty($request->id) == true){
			return response()->json([
				'error' => [
					'message' => '문의 ID 를 입력해주세요'
				],
			]);
		}

		$contact = Contact::find($request->id);
		if(empty($contact) == true){
			return response()->json([
				'error' => [
					'message' => '문의를 찾을수 없습니다'
				],
			]);
		}

		$taget = [[
			'email' => $contact->email
		]];

		Mail::to($taget)->send(
			new AmazonSes(
				[
					'subject' => "Re: " . $contact->subject,
					'content' => $request->reply
				]
			)
		);

		$contact->reply =  $request->reply;
		$contact->save();

		return response()->json([
			'data' => true
		]);
	}
}
