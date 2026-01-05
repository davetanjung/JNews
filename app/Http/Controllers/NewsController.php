<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\WeeklySummary;
use App\Models\UserSummary;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
                // First, check if we have ANY articles for this category (not just this week)
                $totalArticles = Article::where('category', $category)->count();
                
                if ($totalArticles === 0) {
                    throw new \Exception("No articles exist in this category at all");
                }

                // Try to get articles from this week
                $articleQuery = Article::whereBetween('publishedAt', [
                    $now->copy()->startOfWeek()->timezone('UTC'),
                    $now->copy()->endOfWeek()->timezone('UTC')
                ])->where('category', $category);

                $articlesForAI = $articleQuery->latest()->limit(20)->get(['id', 'title', 'description']);

                // If no articles this week, get recent articles instead
                if ($articlesForAI->isEmpty()) {
                    Log::info("No articles this week for {$category}, using recent articles");
                    $articlesForAI = Article::where('category', $category)
                        ->latest('publishedAt')
                        ->limit(20)
                        ->get(['id', 'title', 'description']);
                }

                if ($articlesForAI->isNotEmpty()) {
                    $list = $articlesForAI->map(function ($a) {
                        return "[{$a->id}] {$a->title}";
                    })->implode("\n");

                    $catName = $category ? ucfirst($category) : "General";

                    $prompt = "Analyze these {$catName} headlines and create 4-5 subtopics. Return ONLY valid JSON:
                    { \"subcategories\": [\"topic-1\"], \"article_mapping\": { \"topic-1\": [1] } }
                    Rules: Use lowercase-with-hyphens. Map each article ID to the most relevant topic.
                    Headlines: {$list}";

                    $aiResponse = $gemini->generateSummary($prompt);
                    $cleanedResponse = preg_replace('/```json\s*|\s*```/', '', trim($aiResponse));
                    $aiData = json_decode($cleanedResponse, true);

                    Log::info('AI Response for subcategories:', [
                        'raw' => $aiResponse,
                        'cleaned' => $cleanedResponse,
                        'decoded' => $aiData
                    ]);

                    if (is_array($aiData) && isset($aiData['subcategories']) && isset($aiData['article_mapping'])) {
                        $subcategories = array_filter($aiData['subcategories'], fn($s) => !in_array(strtolower($s), ['general', 'news']));

                        // Store in cache
                        $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                        Cache::put($cacheKey, [
                            'subcategories' => $subcategories,
                            'mapping' => $aiData['article_mapping']
                        ], now()->addWeek());

                        // Store in database with article IDs
                        foreach ($subcategories as $subcat) {
                            $articleIds = $aiData['article_mapping'][$subcat] ?? [];
                            
                            Log::info("Storing subcategory: {$subcat}", ['article_ids' => $articleIds]);
                            
                            WeeklySummary::updateOrCreate(
                                [
                                    'year' => $currentYear,
                                    'week_number' => $currentWeek,
                                    'category' => $category,
                                    'subcategory' => $subcat
                                ],
                                [
                                    'summary_content' => null,
                                    'article_ids' => json_encode($articleIds)
                                ]
                            );
                        }
                    } else {
                        throw new \Exception("Invalid AI response format");
                    }
                } else {
                    throw new \Exception("No articles found in category");
                }
            } catch (\Exception $e) {
                Log::error('Subcategory generation failed: ' . $e->getMessage());
                
                // Use fallback subcategories
                $subcategories = $this->getFallbackSubcategories($category);
                
                // Get ALL articles from this category (any time period)
                $allArticleIds = Article::where('category', $category)
                    ->latest('publishedAt')
                    ->limit(50)
                    ->pluck('id')
                    ->toArray();
                
                Log::info("Using fallback subcategories with {count} articles", [
                    'count' => count($allArticleIds),
                    'subcategories' => $subcategories
                ]);
                
                // Store fallback subcategories with all available articles
                foreach ($subcategories as $subcat) {
                    WeeklySummary::updateOrCreate(
                        [
                            'year' => $currentYear,
                            'week_number' => $currentWeek,
                            'category' => $category,
                            'subcategory' => $subcat
                        ],
                        [
                            'summary_content' => null,
                            'article_ids' => json_encode($allArticleIds)
                        ]
                    );
                }
            }
        }

        // Step 4: Generate summary for specific subcategory
        if ($subcategory && ($shouldGenerate || $shouldRegenerate)) {
            try {
                // Try cache first, then database
                $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                $cachedData = Cache::get($cacheKey);
                $articleIds = $cachedData['mapping'][$subcategory] ?? null;

                // If not in cache, get from database
                if (is_null($articleIds)) {
                    $summaryRecord = WeeklySummary::where('year', $currentYear)
                        ->where('week_number', $currentWeek)
                        ->where('category', $category)
                        ->where('subcategory', $subcategory)
                        ->first();

                    if ($summaryRecord && $summaryRecord->article_ids) {
                        $articleIds = json_decode($summaryRecord->article_ids, true);
                    }
                }

                if (!empty($articleIds)) {
                    $articlesForAI = Article::whereIn('id', $articleIds)
                        ->latest('publishedAt')
                        ->limit(10)
                        ->get(['title', 'description']);

                    if ($articlesForAI->isNotEmpty()) {
                        $list = $articlesForAI->map(fn($a) => "- {$a->title}")->implode("\n");

                        $prompt = "Write a brief 100-word summary about '{$subcategory}' news in '{$category}'. Include 1 paragraph and a 3-item list. Headlines: {$list}";

                        $newContent = $gemini->generateSummary($prompt);

                        if (!empty($newContent)) {
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

                            // Save to user history
                            if (Auth::check()) {
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
                            $summary = "Failed to generate summary. Please try again.";
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

        // Article query
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
            // Get article IDs from database
            $summaryRecord = WeeklySummary::where('year', $currentYear)
                ->where('week_number', $currentWeek)
                ->where('category', $category)
                ->where('subcategory', $subcategory)
                ->first();

            $articleIds = null;

            // Try database first
            if ($summaryRecord && $summaryRecord->article_ids) {
                $articleIds = json_decode($summaryRecord->article_ids, true);
                Log::info("Found article IDs from database for {$subcategory}", [
                    'count' => count($articleIds ?? [])
                ]);
            }

            // Fallback to cache
            if (empty($articleIds)) {
                $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                $cachedData = Cache::get($cacheKey);
                
                if ($cachedData && isset($cachedData['mapping'][$subcategory])) {
                    $articleIds = $cachedData['mapping'][$subcategory];
                    Log::info("Found article IDs from cache for {$subcategory}", [
                        'count' => count($articleIds)
                    ]);
                }
            }

            // Apply filter
            if (!empty($articleIds) && is_array($articleIds)) {
                $query->whereIn('id', $articleIds);
            } else {
                // Last resort: fuzzy string matching
                Log::warning("No article IDs found for subcategory: {$subcategory}, using fuzzy match");
                $searchTerm = str_replace('-', ' ', $subcategory);
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
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
            'technology' => ['ai-machine-learning', 'mobile-devices', 'cybersecurity', 'software-development'],
            'business' => ['stock-markets', 'cryptocurrency', 'startups', 'economy'],
            'sports' => ['football', 'basketball', 'tennis', 'olympics'],
            'entertainment' => ['movies', 'music', 'tv-shows', 'celebrities'],
            'general' => ['politics', 'world-news', 'local-news', 'breaking-news']
        ];

        return $fallbacks[$category] ?? ['trending', 'top-stories', 'latest-news', 'featured'];
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