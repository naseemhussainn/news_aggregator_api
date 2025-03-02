<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test fetching user preferences
    public function test_index()
    {
        // Create a user
        $user = User::factory()->create();

        // Create test data
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        // Attach preferences to the user
        $user->preferredSources()->attach($source->id, ['preference_type' => 'source']);
        $user->preferredCategories()->attach($category->id, ['preference_type' => 'category']);
        $user->preferredAuthors()->attach($author->id, ['preference_type' => 'author']);
        // Mock the request
        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new UserPreferenceController();
        $response = $controller->index($request);

        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert the response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('sources', $responseData);
        $this->assertArrayHasKey('categories', $responseData);
        $this->assertArrayHasKey('authors', $responseData);
    }

    // Test setting source preferences
    public function test_setSources()
    {
        // Create a user
        $user = User::factory()->create();

        // Create test data
        $source1 = Source::factory()->create();
        $source2 = Source::factory()->create();

        // Mock the request
        $request = new Request([
            'source_ids' => [$source1->id, $source2->id],
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock the Validator facade
        Validator::shouldReceive('make')
                 ->once()
                 ->andReturn(Mockery::mock(['fails' => false]));

        $controller = new UserPreferenceController();
        $response = $controller->setSources($request);

        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert the response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('sources', $responseData);
    }

    // Test setting category preferences
    public function test_setCategories()
    {
        // Create a user
        $user = User::factory()->create();

        // Create test data
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        // Mock the request
        $request = new Request([
            'category_ids' => [$category1->id, $category2->id],
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock the Validator facade
        Validator::shouldReceive('make')
                 ->once()
                 ->andReturn(Mockery::mock(['fails' => false]));

        $controller = new UserPreferenceController();
        $response = $controller->setCategories($request);

        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert the response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('categories', $responseData);
    }

    // Test setting author preferences
    public function test_setAuthors()
    {
        // Create a user
        $user = User::factory()->create();

        // Create test data
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();

        // Mock the request
        $request = new Request([
            'author_ids' => [$author1->id, $author2->id],
        ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock the Validator facade
        Validator::shouldReceive('make')
                 ->once()
                 ->andReturn(Mockery::mock(['fails' => false]));

        $controller = new UserPreferenceController();
        $response = $controller->setAuthors($request);

        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert the response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('authors', $responseData);
    }
}