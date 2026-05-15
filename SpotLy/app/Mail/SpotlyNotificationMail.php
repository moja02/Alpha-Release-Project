<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SpotlyNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailDetails;

    // استقبال بيانات الرسالة (العنوان والنص)
    public function __construct($mailDetails)
    {
        $this->mailDetails = $mailDetails;
    }

    // بناء الرسالة وتوجيهها للقالب
    public function build()
    {
        return $this->subject($this->mailDetails['title'])
                    ->view('emails.spotly_notification');
    }
}