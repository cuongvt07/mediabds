<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
 
class GroqService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    protected array $models = [
        'llama-3.3-70b-versatile',
        'mixtral-8x7b-32768',
        'llama-3.1-8b-instant',
    ];
    protected int $currentModelIndex = 0;
 
    public function __construct()
    {
        $this->apiKey = config('services.groq.key', env('GROQ_API_KEY', ''));
    }
 
    public function chat(array $messages, float $temperature = 0.7, array $tools = [])
    {
        return $this->executeRequest($messages, $temperature, $tools, false);
    }
 
    public function streamChat(array $messages, callable $callback, float $temperature = 0.7)
    {
        $this->currentModelIndex = 0; // Reset for new stream
        $this->attemptStream($messages, $callback, $temperature);
    }
 
    protected function attemptStream(array $messages, callable $callback, float $temperature)
    {
        if (empty($this->apiKey)) return;
        $model = $this->models[$this->currentModelIndex];
 
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'stream' => true,
        ];
 
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => ["Content-Type: application/json", "Authorization: Bearer {$this->apiKey}"],
                'content' => json_encode($payload),
                'ignore_errors' => true
            ]
        ];
 
        $context = stream_context_create($opts);
        $fp = @fopen($this->baseUrl, 'r', false, $context);
 
        if (!$fp) {
            Log::error("Groq Stream Error: Could not open stream to {$this->baseUrl}");
            $callback("⚠️ Lỗi kết nối API. Vui lòng thử lại sau.");
            return;
        }
 
        $meta = stream_get_meta_data($fp);
        $statusLine = $meta['wrapper_data'][0] ?? '';
        
        if (str_contains($statusLine, '429') && $this->currentModelIndex < count($this->models) - 1) {
            $this->currentModelIndex++;
            fclose($fp);
            $this->attemptStream($messages, $callback, $temperature);
            return;
        }
 
        if (!str_contains($statusLine, '200')) {
            Log::error("Groq Stream Error: Status {$statusLine}");
            $callback("⚠️ API trả về lỗi: {$statusLine}");
            fclose($fp);
            return;
        }
 
        while (!feof($fp)) {
            $line = fgets($fp);
            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);
                if (trim($data) === '[DONE]') break;
                $json = json_decode($data, true);
                $content = $json['choices'][0]['delta']['content'] ?? '';
                if ($content) $callback($content);
            }
        }
        fclose($fp);
    }
 
    protected function executeRequest(array $messages, float $temperature, array $tools, bool $stream)
    {
        if (empty($this->apiKey)) return ['error' => 'Groq API Key is missing.'];
 
        foreach ($this->models as $model) {
            try {
                $payload = [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'stream' => $stream
                ];
 
                if (!empty($tools)) {
                    $payload['tools'] = $tools;
                    $payload['tool_choice'] = 'auto';
                }
 
                $response = Http::withToken($this->apiKey)->post($this->baseUrl, $payload);
                
                if ($response->status() === 429) continue;
 
                if ($response->failed()) {
                    $errorData = $response->json();
                    return ['error' => $errorData['error']['message'] ?? 'Unknown API error'];
                }
 
                return $response->json();
            } catch (\Exception $e) {
                if ($model === end($this->models)) {
                    return ['error' => 'Exception: ' . $e->getMessage()];
                }
            }
        }
        return ['error' => 'All models reached rate limits.'];
    }
}
