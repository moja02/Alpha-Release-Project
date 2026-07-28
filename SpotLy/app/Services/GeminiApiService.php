<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    /**
     * Send live database financial stats to Google Gemini API for deep AI synthesis.
     *
     * @param array $metricsData Real calculated database stats
     * @return array|null Returns AI generated narrative & recommendations, or null on failure/no key
     */
    public function generateFinancialAnalysis(array $metricsData): ?array
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (empty($apiKey)) {
            return null; // Fallback smoothly to rule-based engine
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $prompt = "You are SpotLy's Chief AI Financial Analyst for a smart parking network in Tripoli, Libya. Analyze these real database metrics: " 
                . json_encode($metricsData) 
                . " Return a JSON object with two fields: 
                  1) executive_summary: a professional English executive paragraph summarizing financial performance, net revenue, profit margin, and occupancy.
                  2) recommendations: an array of 4 recommendation objects, each with (title, priority, badge_class, border_class, title_class, description).
                  Your output MUST be valid raw JSON only, no markdown formatting.";

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $rawText = $response->json('candidates.0.content.parts.0.text');
                $decoded = json_decode($rawText, true);
                if (is_array($decoded) && isset($decoded['executive_summary'])) {
                    return $decoded;
                }
            } else {
                Log::warning("Gemini API call failed with status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage());
        }

        return null;
    }
}
