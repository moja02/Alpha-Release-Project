<?php

namespace App\Strategies\Export;

/**
 * واجهة استراتيجية التصدير لتطبيق نمط الاستراتيجية (Strategy Pattern) لتصدير البيانات بتنسيقات مختلفة.
 */
interface ExportStrategyInterface
{
    /**
     * تصدير البيانات المحددة وحفظها في ملف بالاسم الممرر.
     *
     * @param array $exportData البيانات المطلوب تصديرها
     * @param string $fileName اسم الملف بدون الامتداد
     * @return \Symfony\Component\HttpFoundation\Response الاستجابة الخاصة بتحميل الملف
     */
    public function export(array $exportData, string $fileName);
}
