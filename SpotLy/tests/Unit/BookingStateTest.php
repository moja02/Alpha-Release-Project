<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Booking;
use App\States\Booking\ConfirmedBookingState;
use App\States\Booking\ActiveBookingState;
use App\States\Booking\CompletedBookingState;
use App\States\Booking\CancelledBookingState;
use Illuminate\Support\Facades\Log;
use Mockery;
use Exception;

class BookingStateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات ConfirmedBookingState
    |--------------------------------------------------------------------------
    */

    public function test_confirmed_state_allows_enter_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'confirmed';
        $booking->shouldReceive('save')->once()->andReturn(true);

        $state = new ConfirmedBookingState();
        $state->enter($booking);

        $this->assertEquals('active', $booking->status);
    }

    public function test_confirmed_state_handles_exception_during_enter(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'confirmed';
        $booking->shouldReceive('save')->once()->andThrow(new Exception('خطأ في الاتصال بقاعدة البيانات'));

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'فشل الانتقال إلى الحالة النشطة في ConfirmedBookingState') 
                    && str_contains($message, 'خطأ في الاتصال بقاعدة البيانات');
            }));

        $state = new ConfirmedBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('خطأ في الاتصال بقاعدة البيانات');

        $state->enter($booking);
    }

    public function test_confirmed_state_allows_cancel_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'confirmed';
        $booking->shouldReceive('save')->once()->andReturn(true);

        $state = new ConfirmedBookingState();
        $state->cancel($booking);

        $this->assertEquals('cancelled', $booking->status);
    }

    public function test_confirmed_state_handles_exception_during_cancel(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'confirmed';
        $booking->shouldReceive('save')->once()->andThrow(new Exception('خطأ في الاتصال بقاعدة البيانات'));

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'فشل إلغاء الحجز في ConfirmedBookingState') 
                    && str_contains($message, 'خطأ في الاتصال بقاعدة البيانات');
            }));

        $state = new ConfirmedBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('خطأ في الاتصال بقاعدة البيانات');

        $state->cancel($booking);
    }

    public function test_confirmed_state_prevents_exit_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $state = new ConfirmedBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('لا يمكن تسجيل الخروج قبل الدخول إلى الموقف!');

        $state->exit($booking);
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات ActiveBookingState
    |--------------------------------------------------------------------------
    */

    public function test_active_state_allows_exit_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'active';
        $booking->shouldReceive('save')->once()->andReturn(true);

        $state = new ActiveBookingState();
        $state->exit($booking);

        $this->assertEquals('completed', $booking->status);
        $this->assertNotNull($booking->end_time);
    }

    public function test_active_state_handles_exception_during_exit(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->status = 'active';
        $booking->shouldReceive('save')->once()->andThrow(new Exception('خطأ في التحديث'));

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'فشل تسجيل الخروج في ActiveBookingState') 
                    && str_contains($message, 'خطأ في التحديث');
            }));

        $state = new ActiveBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('خطأ في التحديث');

        $state->exit($booking);
    }

    public function test_active_state_prevents_enter_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $state = new ActiveBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('السيارة داخل الموقف بالفعل!');

        $state->enter($booking);
    }

    public function test_active_state_prevents_cancel_transition(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $state = new ActiveBookingState();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('لا يمكن إلغاء الحجز والسيارة داخل الموقف!');

        $state->cancel($booking);
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات CompletedBookingState
    |--------------------------------------------------------------------------
    */

    public function test_completed_state_prevents_all_transitions(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $state = new CompletedBookingState();

        // 1. منع الدخول
        try {
            $state->enter($booking);
            $this->fail('Expected exception was not thrown for enter() on CompletedBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز مكتمل بالفعل ولا يمكن إعادة استخدامه!', $e->getMessage());
        }

        // 2. منع الخروج
        try {
            $state->exit($booking);
            $this->fail('Expected exception was not thrown for exit() on CompletedBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز مكتمل بالفعل وقد سجلت السيارة خروجها سابقاً!', $e->getMessage());
        }

        // 3. منع الإلغاء
        try {
            $state->cancel($booking);
            $this->fail('Expected exception was not thrown for cancel() on CompletedBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز مكتمل بالفعل ولا يمكن إلغاؤه!', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات CancelledBookingState
    |--------------------------------------------------------------------------
    */

    public function test_cancelled_state_prevents_all_transitions(): void
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        $state = new CancelledBookingState();

        // 1. منع الدخول
        try {
            $state->enter($booking);
            $this->fail('Expected exception was not thrown for enter() on CancelledBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز ملغي بالفعل ولا يمكن تسجيل دخول السيارة به!', $e->getMessage());
        }

        // 2. منع الخروج
        try {
            $state->exit($booking);
            $this->fail('Expected exception was not thrown for exit() on CancelledBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز ملغي بالفعل!', $e->getMessage());
        }

        // 3. منع الإلغاء المتكرر
        try {
            $state->cancel($booking);
            $this->fail('Expected exception was not thrown for cancel() on CancelledBookingState');
        } catch (Exception $e) {
            $this->assertEquals('الحجز ملغي بالفعل سابقاً!', $e->getMessage());
        }
    }
}
