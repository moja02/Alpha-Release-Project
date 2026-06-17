<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Strategies\Refund\StandardRefundStrategy;
use App\Strategies\Refund\RefundStrategyFactory;
use App\Strategies\Refund\RefundContext;
use App\Strategies\Refund\RefundStrategyInterface;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Mockery;

class RefundStrategyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * اختبار StandardRefundStrategy: إرجاع 100% من المبلغ إذا كان الوقت المتبقي أكبر من 30 دقيقة.
     */
    public function test_standard_refund_strategy_returns_full_amount_when_duration_is_greater_than_30_minutes(): void
    {
        $strategy = new StandardRefundStrategy();
        $originalCost = 100.0;
        
        // 31 دقيقة (أكثر من 30)
        $refund = $strategy->calculateRefund($originalCost, 31);
        $this->assertEquals(100.0, $refund);

        // 60 دقيقة
        $refund = $strategy->calculateRefund($originalCost, 60);
        $this->assertEquals(100.0, $refund);
    }

    /**
     * اختبار StandardRefundStrategy: إرجاع 50% من المبلغ إذا كان الوقت المتبقي 30 دقيقة أو أقل.
     */
    public function test_standard_refund_strategy_returns_half_amount_when_duration_is_30_minutes_or_less(): void
    {
        $strategy = new StandardRefundStrategy();
        $originalCost = 100.0;

        // 30 دقيقة بالضبط
        $refund = $strategy->calculateRefund($originalCost, 30);
        $this->assertEquals(50.0, $refund);

        // 15 دقيقة (أقل من 30)
        $refund = $strategy->calculateRefund($originalCost, 15);
        $this->assertEquals(50.0, $refund);

        // 0 دقيقة
        $refund = $strategy->calculateRefund($originalCost, 0);
        $this->assertEquals(50.0, $refund);
    }

    /**
     * اختبار RefundStrategyFactory: التأكد من أنه عند تمرير أي حجز، يقوم دائماً بإنشاء وإرجاع كائن من نوع StandardRefundStrategy.
     */
    public function test_refund_strategy_factory_always_returns_standard_refund_strategy(): void
    {
        // استخدام Mockery لإنشاء كائن Mock من Booking دون الحاجة للاتصال بقاعدة البيانات
        $bookingMock = Mockery::mock(Booking::class);

        $strategy = RefundStrategyFactory::make($bookingMock);

        $this->assertInstanceOf(StandardRefundStrategy::class, $strategy);
    }

    /**
     * اختبار RefundContext: التأكد من أنه يستدعي الاستراتيجية الممررة له بشكل صحيح ويُرجع النتيجة المالية المحسوبة.
     */
    public function test_refund_context_calls_strategy_correctly(): void
    {
        $originalCost = 150.0;
        $minutesToStart = 45;
        $expectedRefund = 150.0;

        // إنشاء كائن Mock للاستراتيجية
        $strategyMock = Mockery::mock(RefundStrategyInterface::class);
        $strategyMock->shouldReceive('calculateRefund')
            ->once()
            ->with($originalCost, $minutesToStart)
            ->andReturn($expectedRefund);

        $context = new RefundContext($strategyMock);
        $refund = $context->calculateRefund($originalCost, $minutesToStart);

        $this->assertEquals($expectedRefund, $refund);
    }

    /**
     * اختبار RefundContext: التأكد من التعامل مع الاستثناءات وتسجيل الأخطاء وإرجاع القيمة الافتراضية 0.0 في حالة حدوث خطأ.
     */
    public function test_refund_context_handles_exceptions_and_returns_zero(): void
    {
        $originalCost = 150.0;
        $minutesToStart = 45;

        // إعداد Mock للاستراتيجية لرمي استثناء عند استدعائها
        $strategyMock = Mockery::mock(RefundStrategyInterface::class);
        $strategyMock->shouldReceive('calculateRefund')
            ->once()
            ->with($originalCost, $minutesToStart)
            ->andThrow(new \Exception('تعذر حساب المبلغ المسترجع'));

        // محاكاة تسجيل الخطأ Log::error
        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'خطأ داخل سياق الاسترجاع') && str_contains($message, 'تعذر حساب المبلغ المسترجع');
            }));

        $context = new RefundContext($strategyMock);
        $refund = $context->calculateRefund($originalCost, $minutesToStart);

        $this->assertEquals(0.0, $refund);
    }
}
