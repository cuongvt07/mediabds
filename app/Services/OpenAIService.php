<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->model = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));
    }

    /**
     * [PHASE 2] Smart model routing — chọn model theo mode.
     * FAST: gpt-4o-mini (rẻ, nhanh)
     * SMART: gpt-4o (mạnh hơn về phân tích, suy luận)
     * env override: OPENAI_FAST_MODEL / OPENAI_SMART_MODEL
     */
    public function pickModelForMode(string $mode): string
    {
        if (strtoupper($mode) === 'SMART') {
            return config('services.openai.smart_model', env('OPENAI_SMART_MODEL', 'gpt-4o'));
        }
        return config('services.openai.fast_model', env('OPENAI_FAST_MODEL', 'gpt-4o-mini'));
    }

    public function chat(array $messages, array $tools = [], ?string $modelOverride = null)
    {
        return $this->executeRequest($messages, $tools, false, $modelOverride);
    }


    public function streamChat(array $messages, callable $onText, array $tools = [], ?string $modelOverride = null)
    {
        if (empty($this->apiKey)) {
            $onText("⚠️ OpenAI API Key is missing.");
            return [];
        }

        $toolCalls = [];
        $startTime = microtime(true);
        $modelUsed = $modelOverride ?: $this->model;

        try {
            $payload = [
                'model' => $modelUsed,
                'messages' => $messages,
                'stream' => true,
            ];
            if (!empty($tools)) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
                // [FIX] parallel_tool_calls CHỈ hợp lệ khi có tools — gửi không có tools → OpenAI trả 400
                $payload['parallel_tool_calls'] = true;
            }

            $response = Http::withToken($this->apiKey)
                ->withOptions(['stream' => true])
                ->timeout(90)
                ->retry(3, 200)
                ->post($this->baseUrl, $payload);

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$body->eof()) {
                // [PHASE 3] đọc chunk lớn hơn (1024 thay vì 256) giảm số vòng loop
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);

                    if (str_starts_with($line, 'data: ')) {
                        $data = substr($line, 6);
                        if (trim($data) === '[DONE]') break 2;
                        
                        $json = json_decode($data, true);
                        $delta = $json['choices'][0]['delta'] ?? [];

                        // 1. Text Content
                        if (isset($delta['content'])) {
                            $onText($delta['content']);
                        }

                        // 2. Tool Calls
                        if (isset($delta['tool_calls'])) {
                            foreach ($delta['tool_calls'] as $tc) {
                                $index = $tc['index'];
                                if (!isset($toolCalls[$index])) {
                                    $toolCalls[$index] = [
                                        'id' => $tc['id'] ?? '',
                                        'type' => 'function',
                                        'function' => ['name' => '', 'arguments' => '']
                                    ];
                                }
                                if (isset($tc['id'])) $toolCalls[$index]['id'] = $tc['id'];
                                if (isset($tc['function']['name'])) $toolCalls[$index]['function']['name'] .= $tc['function']['name'];
                                if (isset($tc['function']['arguments'])) $toolCalls[$index]['function']['arguments'] .= $tc['function']['arguments'];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("OpenAI Stream Error: " . $e->getMessage());
            $onText("⚠️ Lỗi kết nối API: " . $e->getMessage());
        }

        // [PHASE 3] log performance để theo dõi tốc độ model
        $elapsed = round(microtime(true) - $startTime, 2);
        Log::info("OpenAI stream done", ['model' => $modelUsed, 'elapsed_s' => $elapsed, 'tool_calls' => count($toolCalls)]);

        return array_values($toolCalls);
    }

    protected function executeRequest(array $messages, array $tools, bool $stream, ?string $modelOverride = null)
    {
        if (empty($this->apiKey)) return ['error' => 'OpenAI API Key is missing.'];

        try {
            $payload = [
                'model' => $modelOverride ?: $this->model,
                'messages' => $messages,
                'stream' => $stream
            ];

            if (!empty($tools)) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
            }

            $response = Http::withToken($this->apiKey)
                ->timeout(90)
                ->retry(3, 200)
                ->post($this->baseUrl, $payload);
            
            if ($response->failed()) {
                $errorData = $response->json();
                Log::error("OpenAI API Error", $errorData ?? []);
                return ['error' => $errorData['error']['message'] ?? 'Unknown API error'];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("OpenAI Exception: " . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }
}
