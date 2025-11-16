<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DebugFrontendUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frontend:debug-url 
                           {url : The URL to test}
                           {--timeout=60 : Request timeout}
                           {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug specific frontend URL with detailed logging';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
        $timeout = (int) $this->option('timeout');
        $detailed = $this->option('detailed');
        
        $frontendUrl = rtrim(config('frontend_cache_refresh.frontend_url'), '/');
        $fullUrl = $frontendUrl . $url;
        
        $this->info("🔍 Debugging Frontend URL");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("URL: <comment>{$fullUrl}</comment>");
        $this->line("Timeout: <comment>{$timeout}s</comment>");
        $this->newLine();

        // Test multiple approaches
        $approaches = [
            'standard' => 'Standard Laravel HTTP Client',
            'no_retry' => 'Without retry mechanism',
            'increased_connect_timeout' => 'Increased connect timeout',
            'curl_direct' => 'Direct cURL approach',
        ];

        foreach ($approaches as $key => $description) {
            $this->line("Testing: <fg=cyan>{$description}</fg=cyan>");
            
            try {
                $startTime = microtime(true);
                $result = $this->testApproach($key, $fullUrl, $timeout, $detailed);
                $totalTime = microtime(true) - $startTime;
                
                if ($result['success']) {
                    $this->line("✅ <fg=green>SUCCESS</fg=green> - Time: " . round($totalTime, 3) . "s - Status: {$result['status']}");
                    if ($detailed && isset($result['response_preview'])) {
                        $this->line("   Response preview: " . $result['response_preview']);
                    }
                } else {
                    $this->line("❌ <fg=red>FAILED</fg=red> - Time: " . round($totalTime, 3) . "s");
                    $this->line("   Error: {$result['error']}");
                }
                
            } catch (\Exception $e) {
                $this->line("⚠️  <fg=red>EXCEPTION</fg=red>");
                $this->line("   {$e->getMessage()}");
            }
            
            $this->newLine();
        }

        return 0;
    }

    private function testApproach(string $approach, string $url, int $timeout, bool $detailed): array
    {
        switch ($approach) {
            case 'standard':
                return $this->testStandard($url, $timeout, $detailed);
                
            case 'no_retry':
                return $this->testNoRetry($url, $timeout, $detailed);
                
            case 'increased_connect_timeout':
                return $this->testIncreasedConnectTimeout($url, $timeout, $detailed);
                
            case 'curl_direct':
                return $this->testCurlDirect($url, $timeout, $detailed);
                
            default:
                throw new \InvalidArgumentException("Unknown approach: {$approach}");
        }
    }

    private function testStandard(string $url, int $timeout, bool $detailed): array
    {
        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(10)
                ->retry(2, 1000)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Laravel-Debug/1.0',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'timestamp' => time(),
                    'source' => 'debug-command'
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
                'response_preview' => $detailed ? substr($response->body(), 0, 200) : null,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function testNoRetry(string $url, int $timeout, bool $detailed): array
    {
        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(10)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Laravel-Debug-NoRetry/1.0',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'timestamp' => time(),
                    'source' => 'debug-command-no-retry'
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
                'response_preview' => $detailed ? substr($response->body(), 0, 200) : null,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function testIncreasedConnectTimeout(string $url, int $timeout, bool $detailed): array
    {
        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(30) // Увеличиваем connect timeout
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Laravel-Debug-LongConnect/1.0',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'timestamp' => time(),
                    'source' => 'debug-command-long-connect'
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
                'response_preview' => $detailed ? substr($response->body(), 0, 200) : null,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function testCurlDirect(string $url, int $timeout, bool $detailed): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'timestamp' => time(),
                'source' => 'debug-command-curl'
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Laravel-Debug-Curl/1.0',
                'X-Requested-With: XMLHttpRequest',
            ],
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => $detailed,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'success' => false,
                'status' => null,
                'error' => $error ?: 'cURL failed',
            ];
        }
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status' => $httpCode,
            'error' => $httpCode >= 400 ? $response : null,
            'response_preview' => $detailed ? substr($response, 0, 200) : null,
        ];
    }
}