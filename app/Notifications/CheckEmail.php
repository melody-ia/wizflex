<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable    verify
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
		$code = md5(env("APP_KEY") .  $this->user->email);
		
        return (new MailMessage)
					->subject('이메일 인증링크가 도착하였습니다')
					->greeting('안녕하세요!')
					->salutation('감사합니다')
                    ->line('이메일 인증 링크가 도착하였습니다')
                    ->action('인증', url('/wallet/verify?code=' . $code))
                    ->line('인증 버튼을 클릭하시면 이메일이 인증됩니다');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
