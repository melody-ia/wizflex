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

class ObjectPayloadPacking {
	
	public function multiNft(){
		return new MultiNft();
	}
	
}

trait PayloadPacking {
	
	public function payloadPacking(){
		return new ObjectPayloadPacking();
	}
	
}