<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\WeeklySummary; // <--- Import your new model
use App\Services\GeminiService; // <--- Use Gemini for the summary, not GNews
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SummarizeWeekly extends Command
{
    protected $signature = 'news:summarize-weekly {--force : Force regenerate existing summaries}';

    protected $description = 'Generates AI summaries for all categories for the current week';

    public function handle(GeminiService $gemini)
    {
        $this->info('Starting weekly summarization...');

        // 1. Get the Timeframe
        $now = Carbon::now();
        $year = $now->year;
        $week = $now->weekOfYear;
        
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        $categories = Article::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->push(null);

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            $catName = $category ?? 'General (All News)';
            
            // Check if exists (unless --force is used)
            $exists = WeeklySummary::where('year', $year)
                ->where('week_number', $week)
                ->where('category', $category)
                ->exists();

            if ($exists && !$this->option('force')) {
                // Skip if already done
                $bar->advance();
                continue; 
            }

            // 3. Fetch Articles for this Category & Week
            $query = Article::whereBetween('publishedAt', [$startOfWeek, $endOfWeek]);
            
            if ($category) {
                $query->where('category', $category);
            }

            // Limit to top 15 to save API costs
            $articles = $query->latest()->limit(15)->get(['title', 'description']);

            if ($articles->isEmpty()) {
                $this->warn("\nNo articles found for {$catName}, skipping.");
                $bar->advance();
                continue;
            }

            // 4. Generate with Gemini
            try {
                $list = $articles->map(fn($a) => "- {$a->title}: {$a->description}")->implode("\n");
                $prompt = "Act as a news anchor. Write a cohesive, engaging 2-paragraph weekly summary for {$catName} news based on these headlines. Do not list them; weave them into a narrative:\n\n" . $list;

                $content = $gemini->generateSummary($prompt);

                // 5. Save to Database
                WeeklySummary::updateOrCreate(
                    [
                        'year' => $year,
                        'week_number' => $week,
                        'category' => $category
                    ],
                    ['summary_content' => $content]
                );

            } catch (\Exception $e) {
                $this->error("\nFailed to generate for {$catName}: " . $e->getMessage());
            }

            $bar->advance();
            // Sleep for 2 seconds to avoid hitting Gemini rate limits too fast
            sleep(2);
        }

        $bar->finish();
        $this->newLine();
        $this->info('Weekly summaries generated successfully!');
    }
}