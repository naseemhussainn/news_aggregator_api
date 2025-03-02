<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="ArticleResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="content", type="string", nullable=true),
 *     @OA\Property(property="url", type="string"),
 *     @OA\Property(property="image_url", type="string", nullable=true),
 *     @OA\Property(property="source_id", type="integer"),
 *     @OA\Property(property="category_id", type="integer", nullable=true),
 *     @OA\Property(property="external_id", type="string", nullable=true),
 *     @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="source", ref="#/components/schemas/Source"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category", nullable=true),
 *     @OA\Property(property="authors", type="array", @OA\Items(ref="#/components/schemas/Author"))
 * )
 *
 * @OA\Schema(
 *     schema="Source",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="api_id", type="string", nullable=true),
 *     @OA\Property(property="url", type="string", nullable=true),
 *     @OA\Property(property="api_provider", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Author",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="api_id", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get a list of articles",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search articles by keyword",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter articles by date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter articles by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="source_id",
     *         in="query",
     *         description="Filter articles by source ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="author_id",
     *         in="query",
     *         description="Filter articles by author ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort articles by field (e.g., published_at)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of articles per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of articles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ArticleResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Get a single article by ID",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the article to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article details",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/feed",
     *     summary="Get a personalized feed for the authenticated user",
     *     tags={"Articles"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search articles by keyword",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter articles by date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort articles by field (e.g., published_at)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of articles per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personalized feed of articles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ArticleResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
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