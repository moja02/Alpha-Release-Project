<?php

namespace App\Strategies\Export;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * فئة استراتيجية التصدير بصيغة JSON لتنزيل البيانات كملف نصي مهيكل.
 */
class JsonExportStrategy implements ExportStrategyInterface
{
    /**
     * تصدير البيانات المحددة وحفظها في ملف بالاسم الممرر بصيغة JSON.
     *
     * @param array $exportData البيانات المطلوب تصديرها
     * @param string $fileName اسم الملف بدون الامتداد
     * @return \Symfony\Component\HttpFoundation\Response الاستجابة الخاصة بتحميل ملف JSON
     * @throws Exception
     */
    public function export(array $exportData, string $fileName)
    {
        try {
            // تحويل البيانات إلى نص JSON منسق مع دعم ترميز الحروف العربية لمنع تشفيرها
            $jsonString = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // إرجاع استجابة تحميل الملف بالترويسات الصحيحة وتحديد اسم الملف الناتج
            $jsonResponse = response($jsonString, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '.json"',
            ]);

            return $jsonResponse;
        } catch (Exception $exception) {
            // تسجيل الخطأ بالتفصيل لمساعدة المطورين في الصيانة والتصحيح
            Log::error("خطأ أثناء تصدير ملف JSON في JsonExportStrategy: " . $exception->getMessage());
            throw $exception;
        }
    }
}
