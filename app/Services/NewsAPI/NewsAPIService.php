<?php

namespace App\Services\NewsAPI;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsAPIService
{
    protected $apiKey;
    protected $baseUrl = 'https://newsapi.org/v2';
    
    public function __construct()
    {
        $this->apiKey = config('services.news_api.key');
    }
    
    /**
     * Fetch articles from NewsAPI.
     *
     * @return array
     */
    public function fetchArticles()
    {
        try {
            $response = Http::get("{$this->baseUrl}/top-headlines", [
                'apiKey' => $this->apiKey,
                'language' => 'en',
                'pageSize' => 100,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['articles']);
            }
            
            Log::error('Failed to fetch articles from NewsAPI: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NewsAPI: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process and save articles from NewsAPI.
     *
     * @param array $articles
     * @return array
     */
    protected function processArticles($articles)
    {
        $savedArticles = [];
        
        foreach ($articles as $articleData) {
            try {
                // Skip if required data is missing
                if (empty($articleData['title']) || empty($articleData['url'])) {
                    continue;
                }
                
                // Process source
                $sourceName = $articleData['source']['name'] ?? 'Unknown';
                $source = Source::firstOrCreate(
                    ['name' => $sourceName, 'api_provider' => 'newsapi'],
                    ['url' => null, 'api_id' => $articleData['source']['id'] ?? null]
                );
                
                // Process category (NewsAPI doesn't provide category, so we'll set a default)
                $category = Category::firstOrCreate(
                    ['slug' => 'general'],
                    ['name' => 'General']
                );
                
                // Check if article already exists
                $existingArticle = Article::where('url', $articleData['url'])->first();
                
                if (!$existingArticle) {
                    // Create new article
                    $article = Article::create([
                        'title' => $articleData['title'],
                        'description' => $articleData['description'],
                        'content' => $articleData['content'],
                        'url' => $articleData['url'],
                        'image_url' => $articleData['urlToImage'] ?? null,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'external_id' => md5($articleData['url']),
                        'published_at' => $articleData['publishedAt'] ? Carbon::parse($articleData['publishedAt']) : now(),
                    ]);
                    
                    // Process author if available
                    if (!empty($articleData['author'])) {
                        $author = Author::firstOrCreate(['name' => $articleData['author']]);
                        $article->authors()->attach($author->id);
                    }
                    
                    $savedArticles[] = $article;
                }
            } catch (\Exception $e) {
                Log::error('Error processing NewsAPI article: ' . $e->getMessage());
                continue;
            }
        }
        
        return $savedArticles;
    }
}