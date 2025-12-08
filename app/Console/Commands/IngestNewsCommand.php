<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Source;
use App\Services\GNewsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon; // <-- Include Carbon for manual formatting
use Illuminate\Support\Facades\Log;
use Throwable;

class IngestNewsCommand extends Command 
{
    protected $signature = 'news:ingest';
    protected $description = 'Fetch top headlines from GNews.io and save/update them in the local database.';

    public function handle(GNewsService $gNewsService)
    {
        $this->info('Starting GNews data ingestion...');
        
        $targets = [
            ['category' => 'general', 'country' => 'id', 'lang' => 'id', 'max' => 50],
            ['category' => 'technology', 'country' => 'us', 'lang' => 'en', 'max' => 50],
            ['category' => 'business', 'country' => 'us', 'lang' => 'en', 'max' => 50],
            // lebih lax, lifestyle
            ['category' => 'sports', 'country' => 'us', 'lang' => 'en', 'max' => 50],
            ['category' => 'entertainment', 'country' => 'us', 'lang' => 'en', 'max' => 50],
            // if mau lebih serious
            // ['category' => 'health', 'country' => 'us', 'lang' => 'en', 'max' => 50],
            // ['category' => 'science', 'country' => 'us', 'lang' => 'en', 'max' => 50],
        ];

        $totalArticlesProcessed = 0;

        foreach ($targets as $target) {
            try {
                $data = $gNewsService->topHeadlines($target);
                $articles = $data['articles'] ?? []; // Ensure $articles is always an array

                if (empty($articles)) {
                    $this->warn("No articles found for target: " . json_encode($target));
                    continue;
                }

                // Use a transaction for atomic batch saving
                DB::transaction(function () use ($articles, &$totalArticlesProcessed) {
                    $articlesToSave = [];
                    $sourcesToSave = [];
                    
                    foreach ($articles as $articleData) { 
                        
                        $sourceId = $articleData['source']['id'];
                        
                        if (!isset($sourcesToSave[$sourceId])) {
                            $sourcesToSave[$sourceId] = [
                                'id' => $sourceId,
                                'name' => $articleData['source']['name'] ?? 'N/A',
                                'url' => $articleData['source']['url'] ?? '',
                                'country' => $articleData['source']['country'] ?? null,
                            ];
                        }

                        // *** FIX: MANUALLY PARSE THE ISO DATE FORMAT USING CARBON ***
                        $publishedAt = Carbon::parse($articleData['publishedAt'])->toDateTimeString();

                        $articlesToSave[] = [
                            'id' => $articleData['id'],
                            'title' => $articleData['title'],
                            'description' => $articleData['description'] ?? null,
                            'content' => $articleData['content'] ?? null,
                            'url' => $articleData['url'],
                            'image' => $articleData['image'] ?? null,
                            'publishedAt' => $publishedAt, // Use the sanitized date string
                            'lang' => $articleData['lang'] ?? null,
                            'source_id' => $sourceId,
                            'category' => $target['category'] ?? null,
                            'created_at' => now(), 
                            'updated_at' => now(),
                        ];
                    }

                    // Batch Source upsert
                    if (!empty($sourcesToSave)) {
                         Source::upsert(array_values($sourcesToSave), ['id'], ['name', 'url', 'country', 'updated_at']);
                    }

                    // Batch Article upsert
                    if (!empty($articlesToSave)) {
                        Article::upsert($articlesToSave, ['id'], [
                            'title', 'description', 'content', 'url', 'image', 'publishedAt', 'lang', 'source_id','category', 'updated_at'
                        ]);
                        $totalArticlesProcessed += count($articlesToSave);
                    }
                });

                $this->info("Successfully processed articles for target: " . json_encode($target));

            } catch (Throwable $e) {
                // If the error is still date-related, it will be caught here
                $this->error("Ingestion failed for target " . json_encode($target) . ": " . $e->getMessage());
                Log::error("GNews Ingestion Error", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        $this->info("GNews ingestion complete. Total articles processed: {$totalArticlesProcessed}");
        return Command::SUCCESS;
    }
}