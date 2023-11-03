<?php


namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Image;

trait FileImageController {


    /**
     * @param $file  	// 리퀘스트의 FILE 객체
     * @param $maxWidth    	// 리사이징할 사이즈
     * @param $saveS3Path   // 저장되는 S3 폴더이름
     * @return bool			// 저장 성공한경우 TRUE를 반환합니다.
     */

    public function saveResizeFile($file,$maxWidth,$saveS3Path,$saveFileName=null)
    {
		if(empty($saveFileName) == true){
			$saveFileName =  Str::random(30).'.'.Str::lower($file->getClientOriginalExtension());
		}

        $ext = Str::lower($file->getClientOriginalExtension());
        if ($ext != "jpg" && $ext != "jpeg" && $ext != "png"){
            return $this->saveS3($saveS3Path,$saveFileName,file_get_contents($file->getRealPath()));
        }

        $originImageSize = GetImageSize($file);
        $width = $originImageSize[0];
        if ($width < $maxWidth){
			return $this->saveS3($saveS3Path,$saveFileName,file_get_contents($file->getRealPath()));
        }

        $imageMake = Image::make($file->getRealPath());
        $imageMake->resize($maxWidth, null, function ($constraint) {
            $constraint->aspectRatio(); // 자동으로 비율 맞춰주기
        });

        $resource = $imageMake->stream()->detach();
        return $this->saveS3($saveS3Path,$saveFileName,$resource);
    }

	public function saveS3($saveS3Path,$saveFileName,$file){
		if (Storage::disk('s3')->put("/{$saveS3Path}/" . $saveFileName,$file,'public') == false){
			return false;
		}

		return $saveFileName;
	}
/*
/nft-files/
/avatar-files/
*/
}
