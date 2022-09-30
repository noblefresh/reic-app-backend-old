<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Swisssms\SmsMessage;
use Swisssms\BcSmsChannel;

class sendOtp extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $user;
    public $otp;
    
    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', BcSmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('OTP Verification')
            ->markdown('emails.notification.send_otp', ['user' => $this->user, 'otp' => $this->otp]);
    }



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */ /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     *
     */
    public function toSms($notifiable): SmsMessage
    {
        // dd('hello wolrd');
        return (new SmsMessage)
            ->content('Your comformation code is '. $this->otp)
            ->to('08092283337');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
