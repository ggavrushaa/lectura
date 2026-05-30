<?php

namespace App\Services;

use App\Support\LectureSummary;
use App\Support\SummaryOptions;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SummaryService
{
    public function summarize(string $transcript, SummaryOptions $options): LectureSummary
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($options)],
            ['role' => 'user', 'content' => $this->userPrompt($transcript)],
        ];

        $content = $this->call($messages);
        $data = $this->extractJson($content);

        if ($data === null) {
            // repair-retry: просим вернуть строго JSON
            $messages[] = ['role' => 'assistant', 'content' => $content];
            $messages[] = ['role' => 'user', 'content' => 'Верни ТОЛЬКО валидный JSON по схеме, без пояснений и markdown-ограждений.'];
            $data = $this->extractJson($this->call($messages));
        }

        if ($data === null) {
            throw new RuntimeException('OpenRouter вернул невалидный JSON дважды');
        }

        return LectureSummary::fromArray($data);
    }

    private function call(array $messages): string
    {
        $response = $this->client()->post('/chat/completions', [
            'model' => config('services.openrouter.model'),
            'models' => [config('services.openrouter.fallback')],
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ]);

        $response->throw();

        return (string) ($response->json('choices.0.message.content') ?? '');
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);
        // снять возможные ```json ... ```
        $content = preg_replace('/^```(?:json)?|```$/m', '', $content) ?? $content;
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end < $start) {
            return null;
        }
        $json = substr($content, $start, $end - $start + 1);
        $data = json_decode($json, true);

        return is_array($data) && isset($data['title'], $data['sections']) ? $data : null;
    }

    private function systemPrompt(SummaryOptions $o): string
    {
        $detail = match ($o->detailLevel->value) {
            'short' => 'Сжатый конспект: только самое главное.',
            'detailed' => 'Подробный конспект с примерами и пояснениями.',
            default => 'Сбалансированный по подробности конспект.',
        };
        $diagrams = $o->withDiagrams
            ? 'Где это уместно, добавляй в раздел поле diagram_mermaid с КОРРЕКТНОЙ диаграммой Mermaid (graph/flowchart/sequence). Идентификаторы узлов — латиницей, подписи — в кавычках. Если схема не нужна — null.'
            : 'Не добавляй диаграммы (diagram_mermaid всегда null).';

        return <<<PROMPT
        Ты — ассистент, делающий богатые, информативные учебные конспекты из расшифровок
        лекций на русском языке. Пиши понятно, по делу, без «воды» из устной речи.
        {$detail}
        {$diagrams}

        Дополнительно:
        - subject: предмет/тема одним-двумя словами (например «Машинное обучение»).
        - level: уровень лекции — "intro" (вводный), "intermediate" (средний) или "advanced".
        - В content_markdown используй выноски через цитату-блок с префиксом-меткой
          в начале строки: "> [!important] ...", "> [!note] ...", "> [!example] ...",
          "> [!tip] ...". Используй их там, где есть важная мысль, пример или совет.
        - Формулы оформляй в LaTeX: внутри текста $...$, отдельной строкой $$...$$.
        - glossary: ключевые термины С КРАТКИМИ определениями (1-2 предложения каждое).
        - quiz: 3-5 вопросов для самопроверки с короткими ответами.

        Верни СТРОГО JSON-объект по схеме (без markdown-ограждений):
        {
          "title": string,
          "subject": string,
          "level": "intro" | "intermediate" | "advanced",
          "summary": string,                 // 2-4 предложения
          "reading_time_min": number,
          "sections": [{
            "heading": string,
            "content_markdown": string,       // markdown: списки, выделения, выноски, формулы
            "terms": string[],
            "diagram_mermaid": string|null
          }],
          "glossary": [{ "term": string, "definition": string }],
          "key_terms": string[],
          "quiz": [{ "question": string, "answer": string }],
          "takeaways": string[]
        }
        PROMPT;
    }

    private function userPrompt(string $transcript): string
    {
        return "Расшифровка лекции:\n\n".$transcript;
    }

    private function client(): PendingRequest
    {
        $key = config('services.openrouter.key');
        if (empty($key)) {
            throw new RuntimeException('OPENROUTER_API_KEY не задан');
        }

        return Http::baseUrl(config('services.openrouter.base'))
            ->withToken($key)
            ->withHeaders([
                'HTTP-Referer' => config('services.openrouter.site_url'),
                'X-Title' => 'Lectura',
            ])
            ->timeout(300)
            ->retry(2, 3000, throw: false);
    }
}
