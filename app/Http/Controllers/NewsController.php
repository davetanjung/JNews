<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    // The Controller no longer needs $gnews property or __construct() because 
    // it reads directly from the local database (Article model).

    /**
     * Display a paginated list of top headlines from the local database (Source of Truth).
     */
    public function topHeadlines(): JsonResponse
    {
        $cacheKey = 'local_top_headlines';
        $ttl = Carbon::now()->addMinutes(15);

        $articles = Cache::remember($cacheKey, $ttl, function () {
            // Reads from Article Model, eager-loads Source.
            return Article::with('source')
                ->orderBy('publishedAt', 'desc')
                ->limit(50)
                ->get();
        });

        if ($articles->isEmpty()) {
            return response()->json(['message' => 'No articles found. The ingestion command may need to be run.'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'totalResults' => $articles->count(),
            'articles' => $articles,
            'served_from' => Cache::has($cacheKey) ? 'cache' : 'database'
        ]);
    }

    /**
     * Search the local database for articles.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['message' => 'Please provide a search query (q).'], 400);
        }

        $cacheKey = 'search:' . md5($query);
        $ttl = Carbon::now()->addHours(1);

        $articles = Cache::remember($cacheKey, $ttl, function () use ($query) {
            // Searches the local database.
            return Article::with('source')
                ->where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->orderBy('publishedAt', 'desc')
                ->limit(50)
                ->get();
        });

        if ($articles->isEmpty()) {
            return response()->json(['message' => "No articles found matching '{$query}'."], 404);
        }

        return response()->json([
            'status' => 'ok',
            'totalResults' => $articles->count(),
            'articles' => $articles,
            'served_from' => Cache::has($cacheKey) ? 'cache' : 'database'
        ]);
    }

    /**
     * Index method for displaying the main view (placeholder for now).
     */
    public function index()
    {
        $articles = Article::with('source')
            ->orderBy('publishedAt', 'desc')
            ->limit(50)
            ->get();

        return view('news.index', compact('articles'));
    }


}