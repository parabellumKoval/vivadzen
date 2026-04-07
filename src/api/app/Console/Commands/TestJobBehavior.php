<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestJobBehavior extends Command
{
    protected $signature = 'frontend:test-job-behavior {url}';
    protected $description = 'Test the same behavior as WebhookDispatchJob';

    public function handle()
    {
        $unitUrl = $this->argument('url');
        $frontendUrls = $this->frontendUrls();
        $timeout = 30; // Same as in your failing case
        
        $this->info("🔍 Testing Job-identical behavior");
        $this->line("URLs: " . implode(', ', array_map(static fn (string $frontendUrl): string => $frontendUrl . $unitUrl, $frontendUrls)));
        $this->line("Timeout: {$timeout}s");
        $this->newLine();

        try {
            // EXACT same setup as in Job
            $httpClient = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Laravel-Admin-Cache-Refresh/1.0',
                    'X-Requested-With' => 'XMLHttpRequest', 
                    'X-Admin-Cache-Refresh' => 'true',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Connection' => 'close',
                ]);

            $httpClient = $httpClient->timeout($timeout)->connectTimeout(10);
            $httpClient = $httpClient->retry(2, 1000);
            
            $httpClient = $httpClient->withOptions([
                'verify' => false,
                'http_errors' => false,
                'allow_redirects' => true,
                'max_redirects' => 3,
            ]);

            $timestamp = time();
            $startTime = microtime(true);

            $this->info("Sending request with EXACT Job setup...");

            foreach ($frontendUrls as $frontendUrl) {
                $fullUrl = $frontendUrl . $unitUrl;
                $this->line("URL: {$fullUrl}");
                $response = $httpClient->post($fullUrl, [
                    'timestamp' => $timestamp,
                    'source' => 'admin-dashboard'
                ]);

                $responseTime = microtime(true) - $startTime;

                if ($response->successful()) {
                    $this->line("✅ <fg=green>SUCCESS</fg=green> - Status: {$response->status()} - Time: " . round($responseTime, 3) . "s");
                    $this->line("Response size: " . strlen($response->body()) . " bytes");
                    $this->line("Response preview: " . substr($response->body(), 0, 100) . "...");
                } else {
                    $this->line("❌ <fg=red>HTTP ERROR</fg=red> - Status: {$response->status()} - Time: " . round($responseTime, 3) . "s");
                    $this->line("Response: " . $response->body());
                }
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->line("🔌 <fg=red>CONNECTION FAILED</fg=red>");
            $this->line("Error: {$e->getMessage()}");
            
            // Extract curl error code
            if (preg_match('/cURL error (\d+)/', $e->getMessage(), $matches)) {
                $curlError = (int) $matches[1];
                $this->line("cURL Error Code: {$curlError}");
                
                switch ($curlError) {
                    case 28:
                        $this->line("💡 <fg=yellow>Hint: Timeout occurred - operation took longer than {$timeout}s</fg=yellow>");
                        break;
                    case 7:
                        $this->line("💡 <fg=yellow>Hint: Failed to connect to server</fg=yellow>");
                        break;
                    case 6:
                        $this->line("💡 <fg=yellow>Hint: Could not resolve host</fg=yellow>");
                        break;
                }
            }
            
        } catch (\Exception $e) {
            $this->line("⚠️  <fg=red>ERROR</fg=red>");
            $this->line($e->getMessage());
        }

        $this->newLine();
        
        // Now let's test what happens in sync queue vs CLI context
        $this->info("Testing execution context differences...");
        
        $this->line("Max execution time: " . ini_get('max_execution_time'));
        $this->line("Memory limit: " . ini_get('memory_limit'));
        $this->line("Running in CLI: " . (php_sapi_name() === 'cli' ? 'YES' : 'NO'));
        
        return 0;
    }

    private function frontendUrls(): array
    {
        $urls = config('webhooks.frontend_urls', []);

        if (is_string($urls)) {
            $urls = explode(',', $urls);
        }

        if (!is_array($urls) || $urls === []) {
            $fallback = config('webhooks.frontend_url');
            $urls = $fallback ? [$fallback] : [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($url): string => rtrim(trim((string) $url), '/'),
            $urls
        ))));
    }
}
