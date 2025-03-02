<?

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    // Test fetching user preferences
    public function test_fetch_user_preferences()
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create test data
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        // Attach preferences to the user
        $user->preferredSources()->attach($source->id, ['preference_type' => 'source']);
        $user->preferredCategories()->attach($category->id, ['preference_type' => 'category']);
        $user->preferredAuthors()->attach($author->id, ['preference_type' => 'author']);

        // Make a request to the preferences endpoint
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/preferences');

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response structure
        $response->assertJsonStructure([
            'sources',
            'categories',
            'authors',
        ]);
    }

    // Test setting source preferences
    public function test_set_source_preferences()
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create test data
        $source1 = Source::factory()->create();
        $source2 = Source::factory()->create();

        // Make a request to set source preferences
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/preferences/sources', [
                             'source_ids' => [$source1->id, $source2->id],
                         ]);

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response structure
        $response->assertJsonStructure([
            'message',
            'sources',
        ]);
    }

    // Test setting category preferences
    public function test_set_category_preferences()
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create test data
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        // Make a request to set category preferences
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/preferences/categories', [
                             'category_ids' => [$category1->id, $category2->id],
                         ]);

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response structure
        $response->assertJsonStructure([
            'message',
            'categories',
        ]);
    }

    // Test setting author preferences
    public function test_set_author_preferences()
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create test data
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();

        // Make a request to set author preferences
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/preferences/authors', [
                             'author_ids' => [$author1->id, $author2->id],
                         ]);

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response structure
        $response->assertJsonStructure([
            'message',
            'authors',
        ]);
    }
}