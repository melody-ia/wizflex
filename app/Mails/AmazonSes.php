<?php
namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AmazonSes extends Mailable {
    use Queueable, SerializesModels;
    public $email_content;

    public function __construct($email_content) {
        $this->email_content = $email_content;
    }

    public function build() {
		//$this->email_content['content']
        return $this->from(envDB('MAIL_FROM_ADDRESS'),envDB('MAIL_FROM_NAME'))->subject($this->email_content['subject'])
		->view('emails.contact-reply')->with('emailMessage',$this->email_content['content']);
    }
}