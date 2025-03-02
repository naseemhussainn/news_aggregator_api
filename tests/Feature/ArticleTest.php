<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    // Test fetching a list of articles
    public function test_fetch_articles()
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);

        // Authenticate as a user before making the request
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/articles');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // Test fetching a single article
    public function test_fetch_single_article()
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);

        // Authenticate as a user before making the request
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    // Test fetching a personalized feed
    public function test_personalized_feed()
    {
        $user = User::factory()->create();
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);

        // Attach preferences to the user with preference_type values
        $user->preferredSources()->attach($source->id, ['preference_type' => 'source']);
        $user->preferredCategories()->attach($category->id, ['preference_type' => 'category']);
        $user->preferredAuthors()->attach($author->id, ['preference_type' => 'author']);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/feed');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }
}