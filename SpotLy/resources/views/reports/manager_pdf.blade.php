<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقرير المالي والتشغيلي لساحة المدير</title>
    <style>
        body { font-family: 'Xanthi', 'DejaVu Sans', 'Arial', sans-serif; direction: rtl; text-align: right; padding: 20px; color: #1d1d1f; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px solid #0071e3; padding-bottom: 12px; }
        .header h2 { color: #0071e3; margin: 0; font-size: 24px; }
        .meta-info { margin-bottom: 20px; font-size: 13px; background: #f8f9fa; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; }
        
        .section-title { color: #0071e3; margin-top: 25px; margin-bottom: 12px; font-size: 16px; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; }

        .metrics-table { width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 10px; }
        .metric-cell { width: 25%; padding: 12px; background: #eef5fc; border-radius: 8px; text-align: center; border: 1px solid #d0e2f7; }
        .metric-title { font-size: 11px; color: #6e6e73; font-weight: bold; }
        .metric-value { font-size: 18px; font-weight: bold; color: #0071e3; margin-top: 4px; }
        .value-profit { color: #28a745; }
        .value-expense { color: #dc3545; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #dee2e6; padding: 9px; text-align: center; font-size: 12px; }
        table.data-table th { background-color: #0071e3; color: white; }
        table.data-table tr:nth-child(even) { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>🚗 التقرير المالي والتشغيلي الشامل - SpotLy</h2>
        <p>تقرير إشغال وحركة الساحات المدارة للفترة المحددة</p>
    </div>

    <div class="meta-info">
        <p><strong>اسم المدير:</strong> {{ $managerName }}</p>
        <p><strong>الفترة الزمنية:</strong> {{ $filterPeriodLabel }} (من {{ $startDate ?? 'البداية' }} إلى {{ $endDate ?? 'الآن' }})</p>
        <p><strong>تاريخ ووقت التصدير:</strong> {{ date('Y-m-d H:i') }}</p>
    </div>

    <div class="section-title">💰 المؤشرات المالية للفترة</div>
    <table class="metrics-table">
        <tr>
            <td class="metric-cell">
                <div class="metric-title">إجمالي الإيرادات</div>
                <div class="metric-value">{{ number_format($totalRevenue, 2) }} د.ل</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">إجمالي المصاريف</div>
                <div class="metric-value value-expense">{{ number_format($totalExpenses, 2) }} د.ل</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">صافي الأرباح</div>
                <div class="metric-value value-profit">{{ number_format($netProfit, 2) }} د.ل</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">متوسط الدخل اليومي</div>
                <div class="metric-value">{{ number_format($avgDailyIncome, 2) }} د.ل</div>
            </td>
        </tr>
    </table>

    <div class="section-title">📊 المؤشرات التشغيلية ونسبة الإشغال</div>
    <table class="metrics-table">
        <tr>
            <td class="metric-cell">
                <div class="metric-title">عدد السيارات الداخلة</div>
                <div class="metric-value">{{ $carsEntered }} سيارة</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">عدد السيارات الخارجة</div>
                <div class="metric-value">{{ $carsExited }} سيارة</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">إجمالي ساعات الوقوف</div>
                <div class="metric-value">{{ number_format($totalParkingHours, 1) }} ساعة</div>
            </td>
            <td class="metric-cell">
                <div class="metric-title">نسبة إشغال الموقف</div>
                <div class="metric-value">{{ number_format($occupancyRate, 1) }}%</div>
            </td>
        </tr>
    </table>

    <div class="section-title">📈 سيميوليشن حركة السيارات (الدخول والخروج اليومي)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>عدد السيارات الداخلة ⬇️</th>
                <th>عدد السيارات الخارجة ⬆️</th>
                <th>إجمالي الحركة التشغيلية</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entryExitSimulation as $sim)
                <tr>
                    <td><strong>{{ $sim['date'] }}</strong></td>
                    <td><span style="color: #28a745; font-weight: bold;">{{ $sim['entered_count'] }}</span></td>
                    <td><span style="color: #dc3545; font-weight: bold;">{{ $sim['exited_count'] }}</span></td>
                    <td>{{ $sim['entered_count'] + $sim['exited_count'] }} مركبة</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">لا توجد سجلات حركة لهذا النطاق الزمني.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
