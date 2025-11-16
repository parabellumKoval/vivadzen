<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestFrontendConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frontend:test-connection 
                           {--url=* : Specific URLs to test}
                           {--timeout=10 : Connection timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to frontend cache refresh endpoints';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeout = (int) $this->option('timeout');
        $specificUrls = $this->option('url');
        
        $this->info('🧪 Testing Frontend Connection');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (!empty($specificUrls)) {
            $this->testSpecificUrls($specificUrls, $timeout);
        } else {
            $this->testConfiguredUnits($timeout);
        }

        return 0;
    }

    private function testSpecificUrls(array $urls, int $timeout)
    {
        foreach ($urls as $url) {
            $this->testUrl($url, $timeout, 'Custom URL');
        }
    }

    private function testConfiguredUnits(int $timeout)
    {
        $frontendUrl = rtrim(config('frontend_cache_refresh.frontend_url'), '/');
        $units = config('frontend_cache_refresh.units', []);

        $this->info("Frontend base URL: {$frontendUrl}");
        $this->newLine();

        if (empty($units)) {
            $this->warn('No cache refresh units configured.');
            return;
        }

        foreach ($units as $unit) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Testing unit: <comment>{$unit['title']}</comment>");
            
            // Handle both string and array URLs
            $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
            $unitTimeout = $unit['timeout'] ?? $timeout;
            
            if ($unitTimeout === 0) {
                $this->line("⏱️  <fg=yellow>Timeout: Unlimited</fg=yellow>");
                $unitTimeout = 120; // Use reasonable timeout for testing
            } else {
                $this->line("⏱️  Timeout: {$unitTimeout}s");
            }
            
            foreach ($unitUrls as $index => $unitUrl) {
                $fullUrl = $frontendUrl . $unitUrl;
                $urlNumber = count($unitUrls) > 1 ? " (" . ($index + 1) . "/" . count($unitUrls) . ")" : "";
                $this->testUrl($fullUrl, $unitTimeout, $unit['title'] . $urlNumber);
            }
        }

        // Test alternative URLs for Docker environments
        if (strpos($frontendUrl, 'localhost') !== false || strpos($frontendUrl, 'host.docker.internal') !== false) {
            $this->newLine();
            $this->info('🐳 Testing Docker alternative URLs...');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
            $alternativeUrls = [
                str_replace(['localhost', 'host.docker.internal'], 'host.docker.internal', $frontendUrl),
                str_replace(['localhost', 'host.docker.internal'], 'localhost', $frontendUrl),
                str_replace(['localhost', 'host.docker.internal'], '172.17.0.1', $frontendUrl),
                str_replace(['localhost', 'host.docker.internal'], '127.0.0.1', $frontendUrl),
            ];

            $alternativeUrls = array_unique($alternativeUrls);
            
            foreach ($alternativeUrls as $altUrl) {
                if ($altUrl !== $frontendUrl) {
                    // Test with first URL from first unit
                    $firstUnit = $units[0];
                    $firstUnitUrls = is_array($firstUnit['url']) ? $firstUnit['url'] : [$firstUnit['url']];
                    $testUrl = $altUrl . $firstUnitUrls[0];
                    $this->testUrl($testUrl, $timeout, "Alternative ({$altUrl})");
                }
            }
        }
    }

    private function testUrl(string $url, int $timeout, string $description)
    {
        $this->line("Testing: <comment>{$description}</comment>");
        $this->line("URL: <info>{$url}</info>");

        try {
            $startTime = microtime(true);
            
            $response = Http::timeout($timeout)
                ->connectTimeout(5)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Laravel-Cache-Test/1.0',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-Admin-Cache-Refresh' => 'true',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'timestamp' => time(),
                    'source' => 'console-test'
                ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $this->line("✅ <fg=green>SUCCESS</fg=green> - Status: {$response->status()} - Time: {$responseTime}ms");
                
                $responseData = $response->json();
                if ($responseData) {
                    $this->line("   Response: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            } else {
                $this->line("❌ <fg=red>HTTP ERROR</fg=red> - Status: {$response->status()} - Time: {$responseTime}ms");
                $this->line("   Response: {$response->body()}");
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->line("🔌 <fg=red>CONNECTION FAILED</fg=red>");
            $this->line("   Error: {$e->getMessage()}");
            
            // Try to give helpful hints
            if (strpos($e->getMessage(), 'Could not connect') !== false) {
                $this->line("   💡 <fg=yellow>Hint: Check if the frontend server is running and accessible</fg=yellow>");
                
                if (strpos($url, 'localhost') !== false) {
                    $this->line("   💡 <fg=yellow>Hint: If running in Docker, try host.docker.internal instead of localhost</fg=yellow>");
                }
            }
            
        } catch (\Exception $e) {
            $this->line("⚠️  <fg=red>ERROR</fg=red>");
            $this->line("   {$e->getMessage()}");
        }

        $this->newLine();
    }
}