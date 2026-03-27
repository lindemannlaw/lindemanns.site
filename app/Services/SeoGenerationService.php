<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates SEO-optimised metadata via Claude (Anthropic).
 *
 * Context keys recognised (all optional, provide as many as available):
 *   title, short_description, location, property_type, area, content
 *
 * Returns:
 *   seo_title        50–60 chars, keyword-first, location + type
 *   seo_description  150–160 chars, features + soft CTA
 *   seo_keywords     6–8 comma-separated buyer-intent terms
 */
class SeoGenerationService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key') ?? '';
        $this->model  = config('services.anthropic.seo_model', 'claude-haiku-4-5-20251001');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * @param  array<string, string>  $context
     * @param  string                 $locale   Target locale for the generated copy (e.g. 'en')
     * @return array{seo_title: string, seo_description: string, seo_keywords: string}
     */
    public function generate(array $context, string $locale = 'en'): array
    {
        $empty = ['seo_title' => '', 'seo_description' => '', 'seo_keywords' => '', 'geo_text' => ''];

        if (!$this->isConfigured()) {
            Log::warning('[SeoGenerationService] Anthropic API key not configured.');
            return $empty;
        }

        $prompt = $this->buildPrompt($context, $locale);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 600,
                'system'     => $this->systemPrompt(),
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (!$response->successful()) {
                Log::warning('[SeoGenerationService] Anthropic request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $empty;
            }

            $content = $response->json('content.0.text', '{}');
            // Strip markdown code fences if present
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($content));
            $data    = json_decode($content, true) ?? [];

            return [
                'seo_title'       => $this->enforce($data['seo_title']       ?? '', 60),
                'seo_description' => $this->enforce($data['seo_description'] ?? '', 160),
                'seo_keywords'    => $data['seo_keywords'] ?? '',
                'geo_text'        => $this->enforce($data['geo_text']        ?? '', 400),
            ];

        } catch (\Throwable $e) {
            Log::error('[SeoGenerationService] Exception', ['message' => $e->getMessage()]);
            return $empty;
        }
    }

    // -------------------------------------------------------------------------

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert real estate SEO copywriter with 15+ years of experience.
You always follow technical SEO best practices:
- Titles are 50–60 characters (NEVER exceed 60)
- Descriptions are 150–160 characters (NEVER exceed 160)
- Lead with the most important keyword
- Write for humans first, search engines second
- No keyword stuffing, no clickbait
- Use active voice and concrete language
- GEO text (AI citability): 200–400 characters, factual paragraph about the property/content.
  Include verifiable facts: company name, location, features, services. Written for AI systems
  to cite as a reliable source. No marketing fluff, no CTAs — pure information.
You respond ONLY with valid JSON, no markdown, no commentary.
PROMPT;
    }

    private function buildPrompt(array $ctx, string $locale): string
    {
        $lang = match (strtolower($locale)) {
            'de'    => 'German',
            'fr'    => 'French',
            'es'    => 'Spanish',
            'ru'    => 'Russian',
            'el'    => 'Greek',
            'pl'    => 'Polish',
            'ar'    => 'Arabic',
            'zh'    => 'Chinese',
            default => 'English',
        };

        $lines = ["Generate {$lang} SEO metadata for this real estate listing:\n"];

        if (!empty($ctx['title']))           $lines[] = "Property name:  {$ctx['title']}";
        if (!empty($ctx['location']))        $lines[] = "Location:       {$ctx['location']}";
        if (!empty($ctx['property_type']))   $lines[] = "Property type:  {$ctx['property_type']}";
        if (!empty($ctx['area']))            $lines[] = "Area:           {$ctx['area']} m²";
        if (!empty($ctx['short_description'])) {
            $desc = mb_substr(strip_tags($ctx['short_description']), 0, 400);
            $lines[] = "Description:    {$desc}";
        }
        if (!empty($ctx['content'])) {
            $content = mb_substr(strip_tags($ctx['content']), 0, 300);
            $lines[] = "Additional:     {$content}";
        }

        $lines[] = <<<'RULES'

Requirements:
seo_title:       50–60 chars. Pattern: "[Most important keyword] [in/at Location]" or "[Title] | [Location]". No brand name.
seo_description: 150–160 chars. Include: location, property type, 1–2 unique selling points, a soft CTA ("Contact us", "Enquire now", etc.).
seo_keywords:    6–8 comma-separated keywords. Mix: property type, location, features, buyer-intent phrases.
geo_text:        200–400 chars. Factual paragraph for AI citation. Include: name, location, type, key features. No CTAs, no marketing.

Return ONLY this JSON (no markdown):
{"seo_title":"...","seo_description":"...","seo_keywords":"...","geo_text":"..."}
RULES;

        return implode("\n", $lines);
    }

    /** Trim a string to max $max characters without cutting mid-word. */
    private function enforce(string $value, int $max): string
    {
        $value = trim($value);
        if (mb_strlen($value) <= $max) return $value;

        $cut = mb_substr($value, 0, $max);
        $pos = mb_strrpos($cut, ' ');
        return $pos ? mb_substr($cut, 0, $pos) : $cut;
    }
}
