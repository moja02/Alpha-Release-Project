<?php

namespace App\Strategies\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * فئة استراتيجية التصدير بصيغة CSV لتنزيل البيانات كملف جدول بيانات.
 */
class CsvExportStrategy implements ExportStrategyInterface
{
    /**
     * تصدير البيانات المحددة وحفظها في ملف بالاسم الممرر بصيغة CSV.
     *
     * @param array $exportData البيانات المطلوب تصديرها
     * @param string $fileName اسم الملف بدون الامتداد
     * @return StreamedResponse الاستجابة الخاصة بتحميل ملف CSV
     * @throws Exception
     */
    public function export(array $exportData, string $fileName)
    {
        try {
            // إنشاء استجابة متدفقة StreamedResponse للتحميل المباشر وتوفير موارد الذاكرة
            $streamedResponse = new StreamedResponse(function () use ($exportData) {
                // فتح مجرى المخرجات الافتراضي للـ PHP
                $outputHandle = fopen('php://output', 'w');

                // إضافة علامة ترتيب البايتات (BOM) لترميز UTF-8 لضمان قراءة اللغة العربية بشكل صحيح في Microsoft Excel
                fwrite($outputHandle, "\xEF\xBB\xBF");

                // كتابة البيانات صفاً بصف داخل الملف المفتوح
                foreach ($exportData as $dataRow) {
                    fputcsv($outputHandle, (array) $dataRow);
                }

                // إغلاق مجرى المخرجات بعد الانتهاء
                fclose($outputHandle);
            });

            // إعداد ترويسات الملف لتحديد نوع المحتوى وطريقة عرضه وتحميله
            $streamedResponse->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '.csv"');

            return $streamedResponse;
        } catch (Exception $exception) {
            // تسجيل الخطأ بالتفصيل لمساعدة المطورين في الصيانة والتصحيح
            Log::error("خطأ أثناء تصدير ملف CSV في CsvExportStrategy: " . $exception->getMessage());
            throw $exception;
        }
    }
}
