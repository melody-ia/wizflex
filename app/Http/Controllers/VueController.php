<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Models\Nft;
use App\Models\Profile;

class VueController extends Controller
{
	public function getTitle($pageName){
		return  $pageName . " | " . envDB('APP_NAME');
	}
	
	public function main(Request $request){
		$title = envDB('APP_NAME');
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function explorer(Request $request,$tab){
		if($tab == 'all'){
			$title = $this->getTitle('전체 NFT 전체 목록');
		}else if($tab == 'search'){
			$title = $this->getTitle($request->q . ' 검색결과');
		}else{
			$tabName = config('category.' . $tab);
			$title = $this->getTitle($tabName . ' NFT 전체 목록');
		}
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function authors(Request $request,$tab){
		if($tab == 'all'){
			$title = $this->getTitle('모든 저자');
		}else if($tab == 'auth'){
			$title = $this->getTitle('인증된 저자');
		}else if($tab == 'unauth'){
			$title = $this->getTitle('미인증 저자');
		}else{
			abort(404);
		}
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function nft(Request $request,$nft_id){
		$nft = Nft::find($nft_id);
		if(empty($nft) == true){
			abort(404);
		}
		
		$title = $this->getTitle($nft->name);
		$description = $nft->description;
		return view('vue')
		->with('title',$title)
		->with('description',$description);
	}
	
	public function collectionNfts(Request $request,$address,$tab){
		$collection = Profile::where('address',$address)->first();
		
		$nick = empty($collection->name)?$address:$collection->nick;
		$description = empty($collection->description)?'':$collection->description;
		
		if($tab == 'sale'){
			$title = $this->getTitle($nick . ' 판매중인 컬렉션');
		}else if($tab == 'owner'){
			$title = $this->getTitle($nick . ' 소유중인 컬렉션');
		}else if($tab == 'creator'){
			$title = $this->getTitle($nick . ' 제작한 컬렉션');		
		}else{
			abort(404);
		}
		
		
		return view('vue')
		->with('title',$title)
		->with('description',$description);
	}
	
	public function createNft(Request $request,$address){
		$title = $this->getTitle('NFT 만들기');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function setting(Request $request,$address){
		$title = $this->getTitle('설정');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function contacts(Request $request){
		$title = $this->getTitle('문의하기');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function help(Request $request){
		$title = $this->getTitle('수수료 및 FAQ');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function service(Request $request){
		$title = $this->getTitle('서비스 이용 약관');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
	
	public function privacy(Request $request){
		$title = $this->getTitle('개인 정보 처리 방침');
		
		
		return view('vue')
		->with('title',$title)
		->with('description',envDB('BASE_DESCRIPTION'));
	}
}