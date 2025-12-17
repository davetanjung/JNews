<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class ExtendArticles extends Command
{
    protected $signature = 'articles:extend';
    protected $description = 'Generate extended content for articles using Gemini';

    public function handle(GeminiService $gemini)
{
    $articles = Article::all();

    foreach ($articles as $article) {

        Log::info("🔵 Processing article {$article->id}", [
            'content_length' => strlen($article->content),
            'description_length' => strlen($article->description),
        ]);

        // Call Gemini
        $expanded = $gemini->extendContent(
            $article->content,
            $article->description,
            300
        );

        Log::info("🟢 Gemini Response for {$article->id}", [
            'is_null' => $expanded === null,
            'preview' => $expanded ? substr($expanded, 0, 300) : "NULL",
        ]);

        $article->extended_content = $expanded;
        $article->save();

        $this->info("Updated article ID {$article->id}");
    }
    usleep(500 * 1000);

    return Command::SUCCESS;
}

}

