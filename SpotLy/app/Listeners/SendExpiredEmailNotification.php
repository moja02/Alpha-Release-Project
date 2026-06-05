<?php

namespace App\Listeners;

use App\Events\BookingExpiredEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SpotlyNotificationMail;
use Exception;

/**
 * مراقب لإرسال إشعار انتهاء صلاحية الحجز عبر قاعدة البيانات والبريد الإلكتروني.
 */
class SendExpiredEmailNotification
{
    /**
     * معالجة حدث انتهاء صلاحية الحجز لإرسال إشعار للمستخدم غير المحظور.
     *
     * @param \App\Events\BookingExpiredEvent $event كائن الحدث
     * @return void
     * @throws Exception
     */
    public function handle($event): void
    {
        try {
            // جلب ملف المستخدم المرتبط بالحجز
            $user = $event->booking->user;
            if ($user) {
                $user->refresh();
            }
            
            // نرسل الإشعار العادي فقط إذا كان العداد أقل من 3 (أي لن يتم حظره)
            if ($user && $user->fake_booking_count < 3) {
                // جلب الحساب الأساسي لمعرفة البريد الإلكتروني
                $account = $user->account;
                $targetEmail = ($account && isset($account->email)) ? $account->email : null;

                // إدخال إشعار انتهاء الحجز في قاعدة البيانات
                DB::table('notifications')->insert([
                    'user_id' => $event->booking->user_id,
                    'message' => 'انتهت مهلة الحجز المبدئي (20 دقيقة) دون حضورك. تم إلغاء الحجز وتسجيل مخالفة في سجلك.',
                    'type' => 'Booking_Expired',
                    'sent_to_email' => $targetEmail,
                    'created_at' => now()
                ]);

                // إرسال البريد الإلكتروني الفوري
                if ($targetEmail) {
                    $mailData = [
                        'title' => 'إشعار تسجيل مخالفة حجز وهمي ⚠️',
                        'body' => 'لقد انتهت مهلة الحجز المبدئي الخاصة بك دون تأكيد حضورك. تم تسجيل مخالفة في سجلك. نذكرك بأنه عند الوصول لـ 3 مخالفات سيتم حظر الحساب تلقائياً.'
                    ];
                    Mail::to($targetEmail)->send(new SpotlyNotificationMail($mailData));
                }
            }
        } catch (Exception $exception) {
            // توثيق الاستثناء
            Log::error("خطأ أثناء إرسال إشعار انتهاء الحجز في SendExpiredEmailNotification: " . $exception->getMessage());
            throw $exception;
        }
    }
}
