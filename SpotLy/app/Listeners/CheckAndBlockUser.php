<?php

namespace App\Listeners;

use App\Events\BookingExpiredEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SpotlyNotificationMail;
use Exception;

/**
 * مراقب للتحقق من وصول المخالفات للحد الأقصى وحظر حساب المستخدم.
 */
class CheckAndBlockUser
{
    /**
     * معالجة حدث انتهاء الحجز للتحقق من المخالفات وحظر الحساب إذا بلغت 3.
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

            // حظر الحساب إذا بلغت المخالفات 3 أو أكثر
            if ($user && $user->fake_booking_count >= 3) {
                // تحديث حالة السائق إلى محظور
                $user->update(['status' => 'blocked']);

                // جلب الحساب الأساسي لمعرفة البريد الإلكتروني
                $account = $user->account;
                $targetEmail = ($account && isset($account->email)) ? $account->email : null;

                // إدخال إشعار الحظر في قاعدة البيانات
                DB::table('notifications')->insert([
                    'user_id' => $event->booking->user_id,
                    'message' => 'تم حظر حسابك لتجاوز الحد الأقصى للمخالفات (3 مرات حجز وهمي دون حضور).',
                    'type' => 'Account_Blocked',
                    'sent_to_email' => $targetEmail,
                    'created_at' => now()
                ]);

                // إرسال البريد الإلكتروني الفوري للإبلاغ بالحظر
                if ($targetEmail) {
                    $mailData = [
                        'title' => 'تنبيه إداري: تم حظر حسابك 🚫',
                        'body' => 'نعلمك بأنه تم حظر حسابك في نظام SpotLy لتجاوزك الحد الأقصى من المخالفات (3 مرات حجز مبدئي دون الحضور). يرجى مراجعة إدارة المواقف.'
                    ];
                    Mail::to($targetEmail)->send(new SpotlyNotificationMail($mailData));
                }
            }
        } catch (Exception $exception) {
            // توثيق الخطأ
            Log::error("خطأ أثناء حظر المستخدم في CheckAndBlockUser: " . $exception->getMessage());
            throw $exception;
        }
    }
}
