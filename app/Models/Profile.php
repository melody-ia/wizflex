<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use App\Models\CollectionLikeCun;
class Profile extends Model
{
    use HasFactory;
	
    protected $table = 'profile';
	
	protected $fillable = [
        'address',
        'name',
        'description',
        'avatar_image',
        'cover_image',
        'website_url',
        'blog_url',
        'twitter_url',
        'instagram_url',
        'auth',
    ];
	
	public function avatar(){
		if(empty($this->avatar_image) == true){
			return envDB('BASE_IMAGE_URI') . '/img/profile.png';
		}
		
		if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/profile/' . $this->avatar_image)) == false){
			return envDB('BASE_AWS_S3_URI') . '/avatar-files/' . $this->avatar_image;
		}
		
		return envDB('BASE_IMAGE_URI') . Storage::url('profile/' . $this->avatar_image);
	}
	
	public function cover(){
		if(empty($this->cover_image) == true){
			return envDB('BASE_IMAGE_URI') . '/img/profile.png';
		}
		
		if(empty(envDB('IS_AWS_S3')) == false && file_exists(storage_path('app/public/profile/' . $this->cover_image)) == false){
			return envDB('BASE_AWS_S3_URI') . '/cover-files/' . $this->cover_image;
		}
			
		return envDB('BASE_IMAGE_URI') . Storage::url('profile/' . $this->cover_image);
	}
	
	
}
