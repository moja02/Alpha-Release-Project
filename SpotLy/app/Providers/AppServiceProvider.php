<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        try {
            // ربط واجهة مستودع الحجوزات مع التطبيق الفعلي الخاص بها لنمط المستودع (Repository Pattern)
            $this->app->bind(
                \App\Repositories\BookingRepositoryInterface::class,
                \App\Repositories\BookingRepository::class
            );
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ أثناء تسجيل مستودع الحجوزات: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            Schema::defaultStringLength(191);

            // تسجيل حدث انتهاء الحجز مع المراقبين الخاصين به لنمط المراقب بالترتيب الصحيح (Observer Pattern)
            \Illuminate\Support\Facades\Event::listen(
                \App\Events\BookingExpiredEvent::class,
                \App\Listeners\IncrementFakeBookingCounter::class
            );

            \Illuminate\Support\Facades\Event::listen(
                \App\Events\BookingExpiredEvent::class,
                \App\Listeners\SendExpiredEmailNotification::class
            );

            \Illuminate\Support\Facades\Event::listen(
                \App\Events\BookingExpiredEvent::class,
                \App\Listeners\CheckAndBlockUser::class
            );
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ أثناء تشغيل AppServiceProvider boot: " . $exception->getMessage());
            throw $exception;
        }
    }
}
