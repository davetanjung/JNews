<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Source;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    /**
     * Display a paginated list of top headlines from the local database (Source of Truth).
     */
    public function topHeadlines(): JsonResponse
    {
        $cacheKey = 'local_top_headlines';
        $ttl = Carbon::now()->addMinutes(15);

        $articles = Cache::remember($cacheKey, $ttl, function () {
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
     * Index method with search and pagination.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = 12;

        $query = Article::with('source')->orderBy('publishedAt', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('source', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $articles = $query->paginate($perPage);

        return view('news.index', compact('articles', 'search'));
    }


    public function show($id)
    {
        $article = Article::with('source')->findOrFail($id);

        // Get related articles from the same source
        $relatedArticles = Article::with('source')
            ->where('source_id', $article->source_id)
            ->where('id', '!=', $article->id)
            ->orderBy('publishedAt', 'desc')
            ->limit(3)
            ->get();

        return view('news.detail', compact('article', 'relatedArticles'));
    }

    public function extendArticle($id, GeminiService $gemini)
    {
        $article = Article::findOrFail($id);

        // Use Gemini to expand the article content
        $expanded = $gemini->extendContent($article->content, 400);

        // Pass to view
        return view('articles.extended', [
            'article' => $article,
            'expandedContent' => $expanded
        ]);
    }
}