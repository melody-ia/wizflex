<?php
namespace App\Http\Controllers;

use App\Library\ReturnCode;
use App\Mails\AmazonSes;
use App\ThemeBackFunctions;
use App\ThemeBorders;
use App\ThemeColors;
use App\ThemeFontOptions;
use App\ThemeFrontFunctions;
use App\Themes;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;


class MailController extends BaseController
{

    public function test () {
        // single email
        $data = [
            'target_email' => [
                [
                    'name' => '이정욱',
                    'email' => 'ghdqh1515@naver.com'
                ]
            ],
            'subject' => 'asdasd',
            'content' => 'qwfqwf'
        ];

        return $this->sendMail($data);
    }

    /**
     * @param $options
     * $options['target_email']
     * $options['subject']
     * $options['content']
     * @return string
     */
    public function sendMail($options) {

		Mail::to($options['target_email'])->send(
			new AmazonSes(
				[
					'subject' => $options['subject'],
					'content' => $options['content']
				]
			)
		);
		

		exit;
        return 'Done!';
    }

}