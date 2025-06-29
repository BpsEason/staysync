<?php

namespace App\Http\Controllers;

use App\Models\CultureContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class CultureContentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'permission:manage:culture']);
    }

    /**
     * Display a listing of the cultural contents for the current tenant.
     * Optionally filter by language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $language = $request->query('lang');
        $tenantId = tenancy()->tenant->id;
        $cacheKey = "culture:tenant:{$tenantId}:lang:{$language ?? 'all'}";

        // Try to retrieve from cache first
        $contents = Cache::tags(['culture', 'tenant:' . $tenantId])->get($cacheKey);

        if ($contents) {
            Log::info("Cultural content retrieved from cache for tenant {$tenantId} (lang: {$language ?? 'all'}).");
            return response()->json($contents);
        }

        try {
            // Apply TenantScope automatically
            $query = CultureContent::query();

            if ($language) {
                $query->where('language', $language);
            }

            $contents = $query->get();

            // Store in cache for future requests (cache for 1 hour)
            Cache::tags(['culture', 'tenant:' . $tenantId])->put($cacheKey, $contents, now()->addHour());

            Log::info("Cultural content retrieved from DB and cached for tenant {$tenantId} (lang: {$language ?? 'all'}).");
            return response()->json($contents);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cultural contents: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch cultural contents.'], 500);
        }
    }

    /**
     * Store a newly created cultural content in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'language' => 'required|string|in:zh_TW,en,ja',
                'category' => 'nullable|string|max:255',
                'image_url' => 'nullable|url|max:2048',
            ]);

            $cultureContent = CultureContent::create([
                'tenant_id' => tenancy()->tenant->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'language' => $validated['language'],
                'category' => $validated['category'],
                'image_url' => $validated['image_url'],
            ]);

            // Invalidate cache for this tenant
            Cache::tags(['culture', 'tenant:' . tenancy()->tenant->id])->flush();

            Log::info("Cultural content created for tenant " . tenancy()->tenant->id . ": " . $cultureContent->title);
            return response()->json($cultureContent, 201);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to store cultural content: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while saving the content.'], 500);
        }
    }

    // You can add show, update, destroy methods here following similar patterns
    // ensuring tenant_id is always implicitly handled by TenantScope.
}
