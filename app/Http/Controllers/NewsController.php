<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\WeeklySummary;
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
    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $perPage = 12;

    //     $query = Article::with('source')->orderBy('publishedAt', 'desc');

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('title', 'like', '%' . $search . '%')
    //                 ->orWhere('description', 'like', '%' . $search . '%')
    //                 ->orWhereHas('source', function ($q) use ($search) {
    //                     $q->where('name', 'like', '%' . $search . '%');
    //                 });
    //         });
    //     }

    //     $articles = $query->paginate($perPage);

    //     return view('news.index', compact('articles', 'search'));
    // }

    /**
     * Index method with Search, simple Category column filter, and Pagination.
     */

    // In App\Http\Controllers\NewsController.php

    public function index(Request $request, GeminiService $gemini)
    {
        // 1. Get Inputs
        $search = $request->input('search');
        $category = $request->input('category');
        $perPage = 12;

        // ==========================================
        //  SUMMARY LOGIC (Check DB -> Generate -> Save)
        // ==========================================

        $summary = null;
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentWeek = $now->weekOfYear;

        // Check if user clicked a button or if we just need to load an existing one
        $shouldGenerate = $request->input('generate_summary');
        $shouldRegenerate = $request->input('regenerate_summary');

        // A. Always try to fetch existing summary from DB first
        $existingSummary = WeeklySummary::where('year', $currentYear)
            ->where('week_number', $currentWeek)
            ->where('category', $category)
            ->first();

        if ($existingSummary) {
            $summary = $existingSummary->summary_content;
        }

        // B. If user clicked "Generate" (and it's missing) OR "Regenerate" (force update)
        if (($shouldGenerate && !$existingSummary) || $shouldRegenerate) {

            // 1. Get recent articles for context
            $articleQuery = Article::whereBetween('publishedAt', [
                // 🛑 FIX: Ensure UTC timezone for correct DB comparison
                $now->copy()->startOfWeek()->timezone('UTC'),
                $now->copy()->endOfWeek()->timezone('UTC')
            ]);
            if ($category) {
                $articleQuery->where('category', $category);
            }

            $articlesForAI = $articleQuery->latest()->limit(15)->get(['title', 'description']);

            if ($articlesForAI->isNotEmpty()) {
                // 2. Prepare Prompt and Call Gemini
                $list = $articlesForAI->map(fn($a) => "- {$a->title}: {$a->description}")->implode("\n");
                $catName = $category ? ucfirst($category) : "General";
                // Revised Prompt Instruction
                $prompt = "Generate a highly condensed, objective, and journalistic news brief for the '{$catName}' category this week. 

Format the entire output as follows:
1. One concise, three-sentence introductory paragraph.
2. An HTML numbered list (<ol>, <li>) of the top 3-5 key headlines. **Do not use Markdown.**

The entire response must be under 150 words. Do not use greetings or conversational phrases.

Article Headlines for Synthesis:
" . $list;
                // Inside NewsController::index, under section B.

                $newContent = $gemini->generateSummary($prompt);

                $contentToSave = $newContent ?? "AI summary generation failed. Check logs for API errors.";

                WeeklySummary::updateOrCreate(
                    [
                        'year' => $currentYear,
                        'week_number' => $currentWeek,
                        'category' => $category
                    ],
                    // Save the guaranteed non-null string
                    ['summary_content' => $contentToSave]
                );

                $summary = $contentToSave;

                // ... (rest of the else block remains the same)

                // 3. Save to DB (Update or Create)
                // WeeklySummary::updateOrCreate(
                //     [
                //         'year' => $currentYear,
                //         'week_number' => $currentWeek,
                //         'category' => $category
                //     ],
                //     ['summary_content' => $newContent]
                // );

                // $summary = $newContent;
            } else {
                $summary = "Not enough news articles this week to generate a summary.";
            }
        }

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

        if ($category) {
            $query->where('category', $category);
        }

        $articles = $query->paginate($perPage);

        // Final view data
        return view('news.index', [
            'articles' => $articles,
            'search' => $search,
            'activeCategory' => $category, // Used by the category selector component
            'summary' => $summary // Used by the summary box component
        ]);
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
