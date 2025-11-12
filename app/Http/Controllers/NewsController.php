<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Services\GNewsService;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    protected $gnews;

    public function __construct(GNewsService $gnews)
    {
        $this->gnews = $gnews;
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->input('q', 'example');
        $max = intval($request->input('max', 10));
        $lang = $request->input('lang', 'en');

        try {
            $result = $this->gnews->search($q, [
                'max' => $max,
                'lang' => $lang,
                'page' => $request->input('page', 1),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch news',
                'message' => $e->getMessage()
            ], $e->getCode() >= 400 ? $e->getCode() : 500);
        }
    }

    public function topHeadlines(Request $request): JsonResponse
    {
        try {
            $result = $this->gnews->topHeadlines([
                'category' => $request->input('category', 'general'),
                'country' => $request->input('country'),
                'lang' => $request->input('lang', 'en'),
                'max' => $request->input('max', 10),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch headlines',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $news = Article::all();
        return view('news', [
            'news' => $news
        ]);
    }
}
