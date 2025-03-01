<?php

namespace App\Services\Guardian;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuardianService
{
    protected $apiKey;
    protected $baseUrl = 'https://content.guardianapis.com';
    
    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
    }
    
    /**
     * Fetch articles from The Guardian API.
     *
     * @return array
     */
    public function fetchArticles()
    {
        try {
            $response = Http::get("{$this->baseUrl}/search", [
                'api-key' => $this->apiKey,
                'page-size' => 50,
                'show-fields' => 'headline,trailText,body,thumbnail',
                'show-tags' => 'contributor',
                'show-section' => 'true',
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['results']);
            }
            
            Log::error('Failed to fetch articles from The Guardian: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching articles from The Guardian: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process and save articles from The Guardian.
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
                if (empty($articleData['webTitle']) || empty($articleData['webUrl'])) {
                    continue;
                }
                
                // Process source
                $source = Source::firstOrCreate(
                    ['name' => 'The Guardian', 'api_provider' => 'guardian'],
                    ['url' => 'https://www.theguardian.com', 'api_id' => null]
                );
                
                // Process category
                $categoryName = $articleData['sectionName'] ?? 'General';
                $categorySlug = Str::slug($categoryName);
                $category = Category::firstOrCreate(
                    ['slug' => $categorySlug],
                    ['name' => $categoryName]
                );
                
                // Check if article already exists
                $existingArticle = Article::where('url', $articleData['webUrl'])->first();
                
                if (!$existingArticle) {
                    // Create new article
                    $article = Article::create([
                        'title' => $articleData['webTitle'],
                        'description' => $articleData['fields']['trailText'] ?? null,
                        'content' => $articleData['fields']['body'] ?? null,
                        'url' => $articleData['webUrl'],
                        'image_url' => $articleData['fields']['thumbnail'] ?? null,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'external_id' => $articleData['id'],
                        'published_at' => $articleData['webPublicationDate'] ? Carbon::parse($articleData['webPublicationDate']) : now(),
                    ]);
                    
                    // Process authors if available
                    if (!empty($articleData['tags'])) {
                        foreach ($articleData['tags'] as $tag) {
                            if ($tag['type'] === 'contributor') {
                                $author = Author::firstOrCreate(['name' => $tag['webTitle']]);
                                $article->authors()->attach($author->id);
                            }
                        }
                    }
                    
                    $savedArticles[] = $article;
                }
            } catch (\Exception $e) {
                Log::error('Error processing Guardian article: ' . $e->getMessage());
                continue;
            }
        }
        
        return $savedArticles;
    }
}