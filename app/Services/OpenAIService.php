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

    public function chat(array $messages, array $tools = [])
    {
        return $this->executeRequest($messages, $tools, false);
    }


    public function streamChat(array $messages, callable $onText, array $tools = [])
    {
        if (empty($this->apiKey)) {
            $onText("⚠️ OpenAI API Key is missing.");
            return [];
        }

        $toolCalls = [];

        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => true,
            ];
            if (!empty($tools)) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
            }

            $response = Http::withToken($this->apiKey)
                ->withOptions(['stream' => true])
                ->timeout(90)
                ->post($this->baseUrl, $payload);

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$body->eof()) {
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

        return array_values($toolCalls);
    }

    protected function executeRequest(array $messages, array $tools, bool $stream)
    {
        if (empty($this->apiKey)) return ['error' => 'OpenAI API Key is missing.'];

        try {
            $payload = [
                'model' => $this->model,
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
