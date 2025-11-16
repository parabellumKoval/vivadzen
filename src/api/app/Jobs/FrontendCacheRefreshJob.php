<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FrontendCacheRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The cache refresh unit configuration
     *
     * @var array
     */
    protected array $unit;

    /**
     * The timestamp when job was created
     *
     * @var int
     */
    protected int $timestamp;

    /**
     * Create a new job instance.
     *
     * @param array $unit The cache refresh unit configuration
     */
    public function __construct(array $unit)
    {
        $this->unit = $unit;
        $this->timestamp = time();
        
        // Set queue if configured
        // Job will use the default queue
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $frontendUrl = rtrim(config('frontend_cache_refresh.frontend_url'), '/');
            $globalTimeout = config('frontend_cache_refresh.timeout', 30);
            
            // Check for unit-specific timeout, fallback to global timeout
            $timeout = $this->unit['timeout'] ?? $globalTimeout;

            // Convert timeout = 0 to unlimited (no timeout)
            $isUnlimited = ($timeout === 0);

            // If this job is executed synchronously inside an HTTP request (QUEUE_CONNECTION=sync)
            // PHP max_execution_time may abort the script (commonly 30s). For unlimited ops
            // we must lift the PHP execution time limit here. In async workers (CLI) this is
            // usually unnecessary, but calling set_time_limit is safe.
            if ($isUnlimited && function_exists('set_time_limit')) {
                @set_time_limit(0);
                @ini_set('max_execution_time', '0');
                Log::info('Set unlimited execution time for job', [
                    'unit' => $this->unit['title'],
                    'new_max_execution_time' => ini_get('max_execution_time')
                ]);
            }
            
            // For debugging: also add safety margin for non-unlimited operations  
            // that might be close to PHP limits
            if (!$isUnlimited && $timeout >= 25 && php_sapi_name() !== 'cli') {
                $safetyMargin = $timeout + 10; // Add 10 seconds safety margin
                @set_time_limit($safetyMargin);
                Log::info('Extended execution time for long operation', [
                    'unit' => $this->unit['title'],
                    'original_timeout' => $timeout,
                    'safety_margin' => $safetyMargin,
                    'new_max_execution_time' => ini_get('max_execution_time')
                ]);
            }
            
            // Get URLs (support both string and array)
            $unitUrls = is_array($this->unit['url']) ? $this->unit['url'] : [$this->unit['url']];
            $allUrls = [];
            
            // Build all URL combinations (unit URLs + Docker alternatives)
            foreach ($unitUrls as $unitUrl) {
                $fullUrl = $frontendUrl . $unitUrl;
                $urlsToTry = [$fullUrl];
                
                if (strpos($fullUrl, 'localhost') !== false) {
                    // Add host.docker.internal variant for Docker environments
                    $urlsToTry[] = str_replace('localhost', 'host.docker.internal', $fullUrl);
                    // Add host machine IP (common Docker setup)
                    $urlsToTry[] = str_replace('localhost', '172.17.0.1', $fullUrl);
                }
                
                $allUrls[] = [
                    'unit_url' => $unitUrl,
                    'urls_to_try' => $urlsToTry
                ];
            }

            Log::info('Frontend cache refresh job started', [
                'unit' => $this->unit['title'],
                'unit_urls' => $unitUrls,
                'timeout' => $isUnlimited ? 'unlimited' : $timeout,
                'timestamp' => $this->timestamp,
                'total_requests' => count($unitUrls),
                'execution_context' => [
                    'max_execution_time' => ini_get('max_execution_time'),
                    'memory_limit' => ini_get('memory_limit'),
                    'is_cli' => php_sapi_name() === 'cli',
                    'sapi_name' => php_sapi_name(),
                    'queue_connection' => config('queue.default'),
                ]
            ]);

            // Store job start status
            $this->updateJobStatus('running');

            $results = [];
            $overallSuccess = true;

            // Process each unit URL
            foreach ($allUrls as $urlGroup) {
                $unitUrl = $urlGroup['unit_url'];
                $urlsToTry = $urlGroup['urls_to_try'];
                $urlSuccess = false;
                $lastException = null;
                $response = null;

                Log::info('Processing unit URL', [
                    'unit' => $this->unit['title'],
                    'unit_url' => $unitUrl,
                    'urls_to_try' => $urlsToTry
                ]);

                foreach ($urlsToTry as $tryUrl) {
                    try {
                        Log::info('Trying frontend URL', [
                            'url' => $tryUrl, 
                            'unit' => $this->unit['title'],
                            'timeout' => $isUnlimited ? 'unlimited' : $timeout,
                            'connect_timeout' => $isUnlimited ? 60 : 10,
                        ]);
                        
                        $httpClient = Http::acceptJson()
                            ->withHeaders([
                                'User-Agent' => 'Laravel-Admin-Cache-Refresh/1.0',
                                'X-Requested-With' => 'XMLHttpRequest',
                                'X-Admin-Cache-Refresh' => 'true',
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'Connection' => 'close', // Force connection close to avoid keep-alive issues
                            ]);

                        // Apply timeout settings
                        if (!$isUnlimited) {
                            $httpClient = $httpClient->timeout($timeout)->connectTimeout(10);
                        } else {
                            // For unlimited timeout, set a very high value and no connect timeout limit
                            $httpClient = $httpClient->timeout(3600)->connectTimeout(60);
                        }

                        // Add retry only for limited timeout scenarios and shorter timeouts
                        if (!$isUnlimited && $timeout < 60) {
                            $httpClient = $httpClient->retry(2, 1000);
                        }
                        
                        // Add additional curl options for debugging
                        $httpClient = $httpClient->withOptions([
                            'verify' => false, // Disable SSL verification in case of issues
                            'http_errors' => false, // Don't throw exceptions on HTTP errors
                            'allow_redirects' => true,
                            'max_redirects' => 3,
                        ]);
                        
                        $startTime = microtime(true);
                        
                        Log::info('Sending request to frontend', [
                            'url' => $tryUrl,
                            'unit' => $this->unit['title'],
                            'headers' => [
                                'User-Agent' => 'Laravel-Admin-Cache-Refresh/1.0',
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                            ],
                            'body' => [
                                'timestamp' => $this->timestamp,
                                'source' => 'admin-dashboard'
                            ]
                        ]);
                        
                        $response = $httpClient->post($tryUrl, [
                            'timestamp' => $this->timestamp,
                            'source' => 'admin-dashboard'
                        ]);

                        $responseTime = microtime(true) - $startTime;
                        
                        Log::info('Received response from frontend', [
                            'url' => $tryUrl,
                            'unit' => $this->unit['title'],
                            'status_code' => $response->status(),
                            'response_time' => round($responseTime, 3),
                            'response_size' => strlen($response->body()),
                            'content_type' => $response->header('Content-Type'),
                        ]);

                        if ($response->successful()) {
                            Log::info('Frontend cache refresh URL completed successfully', [
                                'unit' => $this->unit['title'],
                                'unit_url' => $unitUrl,
                                'successful_url' => $tryUrl,
                                'status_code' => $response->status(),
                                'response_time' => round($responseTime, 3),
                                'response_preview' => substr($response->body(), 0, 200) . (strlen($response->body()) > 200 ? '...' : ''),
                            ]);

                            $results[$unitUrl] = [
                                'status' => 'success',
                                'url' => $tryUrl,
                                'status_code' => $response->status(),
                                'response_body' => $response->json(),
                                'response_time' => round($responseTime, 3),
                            ];
                            
                            $urlSuccess = true;
                            break; // Success - try next unit URL
                        } else {
                            $lastException = new \Exception("HTTP {$response->status()}: " . $response->body());
                            Log::warning("Failed to connect to {$tryUrl}", [
                                'unit' => $this->unit['title'],
                                'unit_url' => $unitUrl,
                                'status_code' => $response->status(),
                                'error' => $lastException->getMessage(),
                                'response_time' => round($responseTime, 3),
                                'response_headers' => $response->headers(),
                                'response_body_preview' => substr($response->body(), 0, 500),
                            ]);
                        }

                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        $lastException = $e;
                        Log::warning("Connection exception for {$tryUrl}", [
                            'unit' => $this->unit['title'],
                            'unit_url' => $unitUrl,
                            'error' => $e->getMessage(),
                            'exception_type' => get_class($e),
                            'curl_error_code' => $this->extractCurlErrorCode($e->getMessage()),
                        ]);
                        continue; // Try next URL
                    } catch (\Exception $e) {
                        $lastException = $e;
                        Log::warning("General exception for {$tryUrl}", [
                            'unit' => $this->unit['title'],
                            'unit_url' => $unitUrl,
                            'error' => $e->getMessage(),
                            'exception_type' => get_class($e),
                        ]);
                        continue; // Try next URL
                    }
                }

                if (!$urlSuccess) {
                    $errorMessage = $lastException ? $lastException->getMessage() : 'All connection attempts failed';
                    $results[$unitUrl] = [
                        'status' => 'failed',
                        'error' => $errorMessage,
                        'tried_urls' => $urlsToTry,
                        'last_status_code' => isset($response) ? $response->status() : null,
                    ];
                    $overallSuccess = false;
                }
            }

            // Update final job status
            if ($overallSuccess) {
                Log::info('All frontend cache refresh URLs completed successfully', [
                    'unit' => $this->unit['title'],
                    'results' => $results,
                ]);

                $this->updateJobStatus('success', [
                    'results' => $results,
                    'total_urls' => count($unitUrls),
                    'successful_urls' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                ]);
            } else {
                Log::warning('Some frontend cache refresh URLs failed', [
                    'unit' => $this->unit['title'],
                    'results' => $results,
                ]);

                $this->updateJobStatus('failed', [
                    'results' => $results,
                    'total_urls' => count($unitUrls),
                    'successful_urls' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                    'failed_urls' => count(array_filter($results, fn($r) => $r['status'] === 'failed')),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Frontend cache refresh job exception', [
                'unit' => $this->unit['title'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->updateJobStatus('failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Frontend cache refresh job failed permanently', [
            'unit' => $this->unit['title'] ?? 'Unknown',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        $this->updateJobStatus('failed', [
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Update job status in cache
     *
     * @param string $status
     * @param array $data
     */
    protected function updateJobStatus(string $status, array $data = [])
    {
        $cacheKey = $this->getJobStatusCacheKey();
        
        $statusData = [
            'status' => $status,
            'unit' => $this->unit,
            'timestamp' => $this->timestamp,
            'updated_at' => time(),
            'data' => $data,
        ];

        // Store for 1 hour
        Cache::put($cacheKey, $statusData, 3600);

        // Also store latest status for this unit type (based on all URLs)
        $unitUrls = is_array($this->unit['url']) ? $this->unit['url'] : [$this->unit['url']];
        $unitIdentifier = md5(json_encode($unitUrls));
        $unitKey = 'frontend_cache_refresh.latest.' . $unitIdentifier;
        Cache::put($unitKey, $statusData, 3600);
    }

    /**
     * Get cache key for job status
     *
     * @return string
     */
    protected function getJobStatusCacheKey(): string
    {
        // Handle both string and array URLs
        $unitUrls = is_array($this->unit['url']) ? $this->unit['url'] : [$this->unit['url']];
        $urlsString = json_encode($unitUrls);
        return 'frontend_cache_refresh.job.' . md5($urlsString . $this->timestamp);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        // Handle both string and array URLs
        $unitUrls = is_array($this->unit['url']) ? $this->unit['url'] : [$this->unit['url']];
        $urlTag = 'cache:' . md5(json_encode($unitUrls));
        
        return [
            'frontend-cache-refresh',
            $urlTag,
        ];
    }

    /**
     * Extract cURL error code from error message
     *
     * @param string $message
     * @return int|null
     */
    private function extractCurlErrorCode(string $message): ?int
    {
        if (preg_match('/cURL error (\d+)/', $message, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}