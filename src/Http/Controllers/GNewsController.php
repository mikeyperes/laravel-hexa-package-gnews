<?php

namespace hexa_package_gnews\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use hexa_package_gnews\Services\GNewsService;
use hexa_core\Models\Setting;

/**
 * GNewsController — handles settings, raw view, and API test endpoints.
 */
class GNewsController extends Controller
{
    /**
     * Show the raw development/test page.
     *
     * @return \Illuminate\View\View
     */
    public function raw()
    {
        return view('gnews::raw.index');
    }

    /**
     * Show the GNews settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('gnews::settings.index', [
            'apiKey' => Setting::getValue('gnews_api_key', ''),
        ]);
    }

    /**
     * Save the GNews API key.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSettings(Request $request)
    {
        $request->validate(['api_key' => 'required|string']);

        Setting::setValue('gnews_api_key', $request->input('api_key'));

        return response()->json([
            'success' => true,
            'message' => 'GNews API key saved successfully.',
            'api_key' => $request->input('api_key'),
        ]);
    }

    /**
     * Test the GNews API key.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testApiKey(Request $request)
    {
        $service = app(GNewsService::class);
        $apiKey = $request->input('api_key') ?: null;
        $result = $service->testApiKey($apiKey);

        return response()->json($result);
    }

    /**
     * Search articles via GNews API (for raw page testing).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchArticles(Request $request)
    {
        $request->validate(['query' => 'required|string']);

        $service = app(GNewsService::class);
        $result = $service->searchArticles(
            $request->input('query'),
            $request->input('max', 10),
            $request->input('lang', 'en')
        );

        return response()->json($result);
    }
}
