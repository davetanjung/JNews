<?php

namespace App\Http\Controllers;

use App\Models\Article;

use App\Models\WeeklySummary;
use App\Models\UserSummary; // <--- 1. Import the model
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // <--- 2. Import Auth

class NewsController extends Controller
{
    public function index(Request $request, GeminiService $gemini)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $subcategory = $request->input('subcategory');
        $perPage = 12;

        $summary = null;
        $subcategories = null;
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentWeek = $now->weekOfYear;

        $shouldGenerate = $request->input('generate_summary');
        $shouldRegenerate = $request->input('regenerate_summary');

        // Step 1: Check if subcategories already exist in DB
        $existingSubcategories = WeeklySummary::where('year', $currentYear)
            ->where('week_number', $currentWeek)
            ->where('category', $category)
            ->whereNull('summary_content')
            ->whereNotNull('subcategory')
            ->pluck('subcategory')
            ->toArray();

        if (!empty($existingSubcategories)) {
            $subcategories = $existingSubcategories;
        }

        // Step 2: Check if summary exists for selected subcategory
        if ($subcategory) {
            $existingSummary = WeeklySummary::where('year', $currentYear)
                ->where('week_number', $currentWeek)
                ->where('category', $category)
                ->where('subcategory', $subcategory)
                ->whereNotNull('summary_content')
                ->first();

            if ($existingSummary) {
                $summary = $existingSummary->summary_content;
            }
        }

        // Step 3: Generate subcategories
        if ($shouldGenerate && empty($subcategories) && !$subcategory) {
            try {
                $articleQuery = Article::whereBetween('publishedAt', [
                    $now->copy()->startOfWeek()->timezone('UTC'),
                    $now->copy()->endOfWeek()->timezone('UTC')
                ]);

                if ($category) {
                    $articleQuery->where('category', $category);
                }

                $articlesForAI = $articleQuery->latest()->limit(20)->get(['id', 'title', 'description']);

                if ($articlesForAI->isNotEmpty()) {
                    $list = $articlesForAI->map(function ($a) {
                        $shortDesc = strlen($a->description) > 100
                            ? substr($a->description, 0, 100) . '...'
                            : $a->description;
                        return "[{$a->id}] {$a->title}";
                    })->implode("\n");

                    $catName = $category ? ucfirst($category) : "General";

                    $prompt = "Analyze these {$catName} headlines and create 4-5 subtopics. Return ONLY valid JSON:
                    { \"subcategories\": [\"topic-1\"], \"article_mapping\": { \"topic-1\": [1] } }
                    Rules: Use lowercase-with-hyphens.
                    Headlines: {$list}";

                    $aiResponse = $gemini->generateSummary($prompt);
                    $cleanedResponse = preg_replace('/```json\s*|\s*```/', '', trim($aiResponse));
                    $aiData = json_decode($cleanedResponse, true);

                    if (is_array($aiData) && isset($aiData['subcategories']) && isset($aiData['article_mapping'])) {
                        $subcategories = array_filter($aiData['subcategories'], fn($s) => !in_array(strtolower($s), ['general', 'news']));

                        $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                        Cache::put($cacheKey, [
                            'subcategories' => $subcategories,
                            'mapping' => $aiData['article_mapping']
                        ], now()->addWeek());

                        foreach ($subcategories as $subcat) {
                            WeeklySummary::updateOrCreate(
                                ['year' => $currentYear, 'week_number' => $currentWeek, 'category' => $category, 'subcategory' => $subcat],
                                ['summary_content' => null]
                            );
                        }
                    } else {
                        throw new \Exception("Invalid AI response format");
                    }
                } else {
                    throw new \Exception("No articles found");
                }
            } catch (\Exception $e) {
                Log::error('Subcategory generation failed: ' . $e->getMessage());
                $subcategories = $this->getFallbackSubcategories($category);
                // (Fallback logic omitted for brevity, keeping your existing logic)
            }
        }

        // Step 4: Generate summary for specific subcategory AND SAVE TO HISTORY
        if ($subcategory && ($shouldGenerate || $shouldRegenerate)) {
            try {
                $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                $cachedData = Cache::get($cacheKey);
                $articleIds = $cachedData['mapping'][$subcategory] ?? [];

                if (!empty($articleIds)) {
                    $articlesForAI = Article::whereIn('id', $articleIds)->latest('publishedAt')->limit(10)->get(['title']);

                    if ($articlesForAI->isNotEmpty()) {
                        $list = $articlesForAI->map(fn($a) => "- {$a->title}")->implode("\n");

                        $prompt = "Write a brief 100-word summary about '{$subcategory}' news in '{$category}'. Include 1 paragraph and a 3-item list. Headlines: {$list}";

                        $newContent = $gemini->generateSummary($prompt);

                        if (!empty($newContent)) {
                            // A. Save to Global Weekly Summary (Your existing logic)
                            WeeklySummary::updateOrCreate(
                                [
                                    'year' => $currentYear,
                                    'week_number' => $currentWeek,
                                    'category' => $category,
                                    'subcategory' => $subcategory
                                ],
                                ['summary_content' => $newContent]
                            );

                            $summary = $newContent;

                            // B. Save to User History (NEW LOGIC)
                            if (Auth::check()) {
                                // Optional: Prevent saving duplicates if they just generated the exact same text
                                $lastSummary = UserSummary::where('user_id', Auth::id())
                                    ->latest()
                                    ->first();

                                if (!$lastSummary || $lastSummary->summary_content !== $newContent) {
                                    UserSummary::create([
                                        'user_id' => Auth::id(),
                                        'summary_content' => $newContent
                                    ]);
                                }
                            }
                        } else {
                            Log::error("Gemini returned empty response for $subcategory");
                            $summary = "Failed to generate summary. Please click Generate again.";
                        }
                    } else {
                        $summary = "No articles found for this topic.";
                    }
                } else {
                    $summary = "No articles available for this topic.";
                }
            } catch (\Exception $e) {
                Log::error('Summary generation failed: ' . $e->getMessage());
                $summary = "Unable to generate summary. Please try again.";
            }
        }

        // Article query (standard)
        $query = Article::with('source')->orderBy('publishedAt', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($subcategory) {
            // (Subcategory filter logic remains the same)
            $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
            $cachedData = Cache::get($cacheKey);
            if ($cachedData && isset($cachedData['mapping'][$subcategory])) {
                $query->whereIn('id', $cachedData['mapping'][$subcategory]);
            } else {
                $query->where('title', 'like', "%" . str_replace('-', ' ', $subcategory) . "%");
            }
        }

        $articles = $query->paginate($perPage);

        return view('news.index', [
            'articles' => $articles,
            'search' => $search,
            'activeCategory' => $category,
            'activeSubcategory' => $subcategory,
            'subcategories' => $subcategories,
            'summary' => $summary
        ]);
    }

    private function getFallbackSubcategories($category)
    {
        $fallbacks = [
            'technology' => ['ai-technology', 'mobile-devices', 'cybersecurity', 'software'],
            'business' => ['stock-markets', 'crypto', 'startups', 'economy'],
            'sports' => ['football', 'basketball', 'tennis', 'olympics'],
            'entertainment' => ['movies', 'music', 'tv-shows', 'celebrities'],
            'general' => ['politics', 'world-news', 'local', 'breaking-news']
        ];

        return $fallbacks[$category] ?? ['trending', 'top-stories', 'latest', 'featured'];
    }

    public function show($id)
    {
        $article = Article::with('source')->findOrFail($id);

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
        $expanded = $gemini->extendContent($article->content, 400);

        return view('articles.extended', [
            'article' => $article,
            'expandedContent' => $expanded
        ]);
    }

    public function mySummaries()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $summaries = UserSummary::where('user_id', Auth::id())
            ->latest()
            ->paginate(9);

        return view('news.my-summaries', compact('summaries'));
    }
}
