<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class FetchGNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-g-news-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news data daily';
    protected $gnews;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching latest news from GNews...');

        try {
            $response = $this->gnews->topHeadlines([
                'lang' => 'en',
                'max' => 10,
            ]);

            if (!isset($response['articles'])) {
                $this->error('No articles found.');
                return;
            }

            $count = 0;
            foreach ($response['articles'] as $article) {
                Article::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'] ?? '',
                        'description' => $article['description'] ?? '',
                        'content' => $article['content'] ?? '',
                        'published_at' => $article['publishedAt'] ?? now(),
                        'source' => $article['source']['name'] ?? '',
                        'image_url' => $article['image'] ?? null,
                    ]
                );
                $count++;
            }

            $this->info("Successfully fetched and stored {$count} articles.");
        } catch (\Exception $e) {
            $this->error('Failed to fetch news: ' . $e->getMessage());
        }
    }
}
