<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LateExitNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $penaltyPoints;

    public function __construct($booking, $penaltyPoints)
    {
        $this->booking = $booking;
        $this->penaltyPoints = $penaltyPoints;
    }

    public function build()
    {
        return $this->subject('تنبيه: تم تسجيل تأخير في حجزك للموقف')
                    ->view('emails.late_exit'); // سننشئ هذا الملف بعد قليل
    }
}