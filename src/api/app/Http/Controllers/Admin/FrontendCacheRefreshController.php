<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\FrontendCacheRefreshJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FrontendCacheRefreshController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Trigger cache refresh for a specific unit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'unit_url' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters',
                    'errors' => $validator->errors()
                ], 422);
            }

            $unitUrl = $request->input('unit_url');
            $units = config('frontend_cache_refresh.units', []);
            
            // Find the unit configuration by checking both string and array URLs
            $unit = null;
            foreach ($units as $candidateUnit) {
                $candidateUrls = is_array($candidateUnit['url']) ? $candidateUnit['url'] : [$candidateUnit['url']];
                if (in_array($unitUrl, $candidateUrls)) {
                    $unit = $candidateUnit;
                    break;
                }
            }
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cache refresh unit not found'
                ], 404);
            }

            // Generate a unique cache key for this unit (based on all URLs)
            $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
            $unitIdentifier = md5(json_encode($unitUrls));
            $latestStatusKey = 'frontend_cache_refresh.latest.' . $unitIdentifier;
            $latestStatus = Cache::get($latestStatusKey);
            
            if ($latestStatus && $latestStatus['status'] === 'running') {
                $timeSinceStart = time() - $latestStatus['timestamp'];
                if ($timeSinceStart < 60) { // Don't allow new jobs if one started less than 1 minute ago
                    return response()->json([
                        'success' => false,
                        'message' => 'Cache refresh for this unit is already in progress',
                        'data' => [
                            'running_since' => $timeSinceStart,
                            'unit' => $unit['title']
                        ]
                    ], 409);
                }
            }

            // Dispatch the job
            $job = new FrontendCacheRefreshJob($unit);
            
            if (config('queue.default') === 'sync') {
                // For sync queue, dispatch and return success
                dispatch($job);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cache refresh completed successfully',
                    'data' => [
                        'unit' => $unit['title'],
                        'urls' => $unitUrls,
                        'total_urls' => count($unitUrls),
                        'mode' => 'synchronous'
                    ]
                ]);
            } else {
                // For async queue, dispatch and return job info
                dispatch($job);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cache refresh job has been queued',
                    'data' => [
                        'unit' => $unit['title'],
                        'urls' => $unitUrls,
                        'total_urls' => count($unitUrls),
                        'mode' => 'asynchronous',
                        'timestamp' => time()
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Frontend cache refresh controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while triggering cache refresh',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status of cache refresh operations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $units = config('frontend_cache_refresh.units', []);
            $statuses = [];

            foreach ($units as $unit) {
                $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
                $unitIdentifier = md5(json_encode($unitUrls));
                $latestStatusKey = 'frontend_cache_refresh.latest.' . $unitIdentifier;
                $latestStatus = Cache::get($latestStatusKey);
                
                $statuses[] = [
                    'unit' => $unit,
                    'urls' => $unitUrls,
                    'total_urls' => count($unitUrls),
                    'status' => $latestStatus['status'] ?? 'never_run',
                    'last_run' => $latestStatus['timestamp'] ?? null,
                    'last_updated' => $latestStatus['updated_at'] ?? null,
                    'data' => $latestStatus['data'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $statuses
            ]);

        } catch (\Exception $e) {
            Log::error('Frontend cache refresh status error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed status for a specific unit
     *
     * @param Request $request
     * @param string $unitUrl
     * @return JsonResponse
     */
    public function unitStatus(Request $request, string $unitUrl): JsonResponse
    {
        try {
            $units = config('frontend_cache_refresh.units', []);
            $unit = null;
            
            // Find unit by checking if unitUrl is in unit's URL(s)
            foreach ($units as $candidateUnit) {
                $candidateUrls = is_array($candidateUnit['url']) ? $candidateUnit['url'] : [$candidateUnit['url']];
                if (in_array($unitUrl, $candidateUrls)) {
                    $unit = $candidateUnit;
                    break;
                }
            }
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cache refresh unit not found'
                ], 404);
            }

            $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
            $unitIdentifier = md5(json_encode($unitUrls));
            $latestStatusKey = 'frontend_cache_refresh.latest.' . $unitIdentifier;
            $latestStatus = Cache::get($latestStatusKey);

            return response()->json([
                'success' => true,
                'data' => [
                    'unit' => $unit,
                    'urls' => $unitUrls,
                    'total_urls' => count($unitUrls),
                    'status' => $latestStatus['status'] ?? 'never_run',
                    'last_run' => $latestStatus['timestamp'] ?? null,
                    'last_updated' => $latestStatus['updated_at'] ?? null,
                    'details' => $latestStatus['data'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Frontend cache refresh unit status error', [
                'unit_url' => $unitUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching unit status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear status cache for all units
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearStatusCache(Request $request): JsonResponse
    {
        try {
            $units = config('frontend_cache_refresh.units', []);
            $cleared = 0;

            foreach ($units as $unit) {
                $unitUrls = is_array($unit['url']) ? $unit['url'] : [$unit['url']];
                $unitIdentifier = md5(json_encode($unitUrls));
                $latestStatusKey = 'frontend_cache_refresh.latest.' . $unitIdentifier;
                if (Cache::forget($latestStatusKey)) {
                    $cleared++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleared status cache for {$cleared} units",
                'data' => [
                    'cleared_count' => $cleared,
                    'total_units' => count($units)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Frontend cache refresh clear status error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing status cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}