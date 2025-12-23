<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\WeeklySummary;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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

        // Step 3: Generate subcategories if user clicked "Generate" and none exist
        // BUT: Only if no subcategory is selected (otherwise we're generating summary, not topics)
        if ($shouldGenerate && empty($subcategories) && !$subcategory) {
            try {
                $articleQuery = Article::whereBetween('publishedAt', [
                    $now->copy()->startOfWeek()->timezone('UTC'),
                    $now->copy()->endOfWeek()->timezone('UTC')
                ]);
                
                if ($category) {
                    $articleQuery->where('category', $category);
                }

                // REDUCED: Only take 20 most recent articles for faster processing
                $articlesForAI = $articleQuery->latest()->limit(20)->get(['id', 'title', 'description']);

                if ($articlesForAI->isNotEmpty()) {
                    // Create a more concise list for AI
                    $list = $articlesForAI->map(function($a) {
                        $shortDesc = strlen($a->description) > 100 
                            ? substr($a->description, 0, 100) . '...' 
                            : $a->description;
                        return "[{$a->id}] {$a->title}";
                    })->implode("\n");
                    
                    $catName = $category ? ucfirst($category) : "General";
                    
                    // SIMPLIFIED PROMPT - Faster response
                    $prompt = "Analyze these {$catName} headlines and create 4-5 subtopics. Return ONLY valid JSON:

{
  \"subcategories\": [\"topic-1\", \"topic-2\", \"topic-3\", \"topic-4\"],
  \"article_mapping\": {
    \"topic-1\": [1, 5, 12],
    \"topic-2\": [2, 8],
    ...
  }
}

Rules:
- Use lowercase-with-hyphens
- Be specific (e.g., \"ai-technology\", \"smartphone-news\")
- Assign each article ID to ONE subcategory

Headlines:
{$list}";

                    $aiResponse = $gemini->generateSummary($prompt);
                    
                    // Clean and parse
                    $cleanedResponse = preg_replace('/```json\s*|\s*```/', '', trim($aiResponse));
                    $aiData = json_decode($cleanedResponse, true);
                    
                    if (is_array($aiData) && isset($aiData['subcategories']) && isset($aiData['article_mapping'])) {
                        $subcategories = array_filter($aiData['subcategories'], function($subcat) {
                            $generic = ['general', 'news', 'updates', 'other', 'miscellaneous'];
                            return !in_array(strtolower($subcat), $generic);
                        });
                        
                        // Cache the mapping
                        $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                        Cache::put($cacheKey, [
                            'subcategories' => $subcategories,
                            'mapping' => $aiData['article_mapping']
                        ], now()->addWeek());
                        
                        // Save to DB
                        foreach ($subcategories as $subcat) {
                            WeeklySummary::updateOrCreate(
                                [
                                    'year' => $currentYear,
                                    'week_number' => $currentWeek,
                                    'category' => $category,
                                    'subcategory' => $subcat
                                ],
                                ['summary_content' => null]
                            );
                        }
                    } else {
                        throw new \Exception("Invalid AI response format");
                    }
                } else {
                    throw new \Exception("No articles found for this week");
                }
            } catch (\Exception $e) {
                Log::error('Subcategory generation failed: ' . $e->getMessage());
                
                // Use fallback subcategories
                $fallbackSubcats = $this->getFallbackSubcategories($category);
                $subcategories = $fallbackSubcats;
                
                // Create simple mapping - split articles evenly
                $allArticleIds = $articlesForAI->pluck('id')->toArray() ?? [];
                $mapping = [];
                $chunkSize = ceil(count($allArticleIds) / count($fallbackSubcats));
                
                foreach ($fallbackSubcats as $index => $subcat) {
                    $offset = $index * $chunkSize;
                    $mapping[$subcat] = array_slice($allArticleIds, $offset, $chunkSize);
                    
                    WeeklySummary::updateOrCreate(
                        [
                            'year' => $currentYear,
                            'week_number' => $currentWeek,
                            'category' => $category,
                            'subcategory' => $subcat
                        ],
                        ['summary_content' => null]
                    );
                }
                
                // Cache fallback mapping
                $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                Cache::put($cacheKey, [
                    'subcategories' => $subcategories,
                    'mapping' => $mapping
                ], now()->addWeek());
            }
        }

        // Step 4: Generate summary for specific subcategory
        // This triggers when user clicks "Generate Summary" on a selected topic
        if ($subcategory && ($shouldGenerate || $shouldRegenerate)) {
            try {
                // Get article IDs from cache
                $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
                $cachedData = Cache::get($cacheKey);
                
                $articleIds = $cachedData['mapping'][$subcategory] ?? [];
                
                if (!empty($articleIds)) {
                    // Limit to 10 articles for summary generation
                    $articlesForAI = Article::whereIn('id', $articleIds)
                        ->latest('publishedAt')
                        ->limit(10)
                        ->get(['title', 'description']);

                    if ($articlesForAI->isNotEmpty()) {
                        $list = $articlesForAI->map(fn($a) => "- {$a->title}")->implode("\n");
                        
                        $prompt = "Write a brief 100-word summary about '{$subcategory}' news in '{$category}'.

Include:
1. One short paragraph (2-3 sentences)
2. HTML list (<ol><li>) of 3 key points

Headlines:
{$list}";

                        $newContent = $gemini->generateSummary($prompt);
                        $contentToSave = $newContent ?? "Summary generation in progress...";

                        WeeklySummary::updateOrCreate(
                            [
                                'year' => $currentYear,
                                'week_number' => $currentWeek,
                                'category' => $category,
                                'subcategory' => $subcategory
                            ],
                            ['summary_content' => $contentToSave]
                        );

                        $summary = $contentToSave;
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

        // Article query with filters
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

        // Filter by subcategory using cached mapping
        if ($subcategory) {
            $cacheKey = "subcategories_{$currentYear}_{$currentWeek}_{$category}";
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData && isset($cachedData['mapping'][$subcategory])) {
                $articleIds = $cachedData['mapping'][$subcategory];
                $query->whereIn('id', $articleIds);
            } else {
                // Fallback to keyword search
                $keywords = str_replace('-', ' ', $subcategory);
                $query->where(function($q) use ($keywords) {
                    $q->where('title', 'like', "%{$keywords}%")
                      ->orWhere('description', 'like', "%{$keywords}%");
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
}