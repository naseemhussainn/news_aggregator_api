<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cacheKey = 'articles_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Article::with(['source', 'category', 'authors'])
                ->search($request->keyword)
                ->byDate($request->date)
                ->byCategory($request->category_id)
                ->bySource($request->source_id)
                ->byAuthor($request->author_id)
                ->orderBy($request->sort_by ?? 'published_at', $request->sort_direction ?? 'desc');
            
            $perPage = $request->per_page ?? 15;
            $articles = $query->paginate($perPage);
            
            return ArticleResource::collection($articles);
        });
    }
    
    /**
     * Display the specified article.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cacheKey = 'article_' . $id;
        
        return Cache::remember($cacheKey, 1800, function () use ($id) {
            $article = Article::with(['source', 'category', 'authors'])->findOrFail($id);
            return new ArticleResource($article);
        });
    }
    
    /**
     * Display a personalized feed for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function personalizedFeed(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'personalized_feed_' . $user->id . '_' . md5(json_encode($request->all()));
        
        return Cache::remember($cacheKey, 300, function () use ($user, $request) {
            // Get user's preferred source IDs
            $sourceIds = $user->preferredSources()->pluck('sources.id')->toArray();
            
            // Get user's preferred category IDs
            $categoryIds = $user->preferredCategories()->pluck('categories.id')->toArray();
            
            // Get user's preferred author IDs
            $authorIds = $user->preferredAuthors()->pluck('authors.id')->toArray();
            
            // Build query based on user preferences
            $query = Article::with(['source', 'category', 'authors']);
            
            if (!empty($sourceIds)) {
                $query->whereIn('source_id', $sourceIds);
            }
            
            if (!empty($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            }
            
            if (!empty($authorIds)) {
                $query->whereHas('authors', function ($q) use ($authorIds) {
                    $q->whereIn('authors.id', $authorIds);
                });
            }
            
            // If user has no preferences, show latest articles
            if (empty($sourceIds) && empty($categoryIds) && empty($authorIds)) {
                $query = Article::with(['source', 'category', 'authors']);
            }
            
            // Apply additional filters if provided
            $query->search($request->keyword)
                ->byDate($request->date)
                ->orderBy($request->sort_by ?? 'published_at', $request->sort_direction ?? 'desc');
            
            $perPage = $request->per_page ?? 15;
            $articles = $query->paginate($perPage);
            
            return ArticleResource::collection($articles);
        });
    }
}