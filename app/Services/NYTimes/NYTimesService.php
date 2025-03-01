<?php

namespace App\Services\NYTimes;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NYTimesService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.nytimes.com/svc';
    
    public function __construct()
    {
        $this->apiKey = config('services.nytimes.key');
    }
    
    /**
     * Fetch articles from the New York Times API.
     *
     * @return array
     */
    public function fetchArticles()
    {
        try {
            $response = Http::get("{$this->baseUrl}/search/v2/articlesearch.json", [
                'api-key' => $this->apiKey,
                'sort' => 'newest',
                'page' => 0,
                'fl' => 'headline,abstract,web_url,pub_date,byline,section_name,multimedia'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['docs'] ?? []);
            }
            
            Log::error('Failed to fetch articles from NY Times: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NY Times: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process and save articles from the New York Times.
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
                if (empty($articleData['headline']['main']) || empty($articleData['web_url'])) {
                    continue;
                }
                
                // Process source
                $source = Source::firstOrCreate(
                    ['name' => 'The New York Times', 'api_provider' => 'nytimes'],
                    ['url' => 'https://www.nytimes.com', 'api_id' => null]
                );
                
                // Process category
                $categoryName = $articleData['section_name'] ?? 'General';
                $categorySlug = Str::slug($categoryName);
                $category = Category::firstOrCreate(
                    ['slug' => $categorySlug],
                    ['name' => $categoryName]
                );
                
                // Check if article already exists
                $existingArticle = Article::where('url', $articleData['web_url'])->first();
                
                if (!$existingArticle) {
                    // Find image URL
                    $imageUrl = null;
                    if (!empty($articleData['multimedia'])) {
                        foreach ($articleData['multimedia'] as $media) {
                            if (isset($media['url'])) {
                                $imageUrl = 'https://static01.nyt.com/' . $media['url'];
                                break;
                            }
                        }
                    }
                    
                    // Create new article
                    $article = Article::create([
                        'title' => $articleData['headline']['main'],
                        'description' => $articleData['abstract'] ?? null,
                        'content' => null, // NYT API doesn't provide full content in this endpoint
                        'url' => $articleData['web_url'],
                        'image_url' => $imageUrl,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'external_id' => md5($articleData['web_url']),
                        'published_at' => $articleData['pub_date'] ? Carbon::parse($articleData['pub_date']) : now(),
                    ]);
                    
                    // Process authors if available
                    if (!empty($articleData['byline']['person'])) {
                        foreach ($articleData['byline']['person'] as $person) {
                            $authorName = trim(($person['firstname'] ?? '') . ' ' . ($person['lastname'] ?? ''));
                            if (!empty($authorName)) {
                                $author = Author::firstOrCreate(['name' => $authorName]);
                                $article->authors()->attach($author->id);
                            }
                        }
                    }
                    
                    $savedArticles[] = $article;
                }
            } catch (\Exception $e) {
                Log::error('Error processing NY Times article: ' . $e->getMessage());
                continue;
            }
        }
        
        return $savedArticles;
    }
}