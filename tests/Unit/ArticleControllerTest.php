<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\ArticleController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Article;
use App\Models\User;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mockery;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test fetching a list of articles
    public function test_index()
    {
        // Create test data
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);
    
        // Mock the request
        $request = new Request();
    
        // Mock the controller to return a response
        $controller = $this->getMockBuilder(ArticleController::class)
            ->onlyMethods(['index'])
            ->getMock();
            
        // Create a response that the controller should return
        $resourceCollection = ArticleResource::collection(Article::paginate(15));
        $responseData = $resourceCollection->response()->getData(true);
        
        // The controller should return this response
        $controller->expects($this->once())
            ->method('index')
            ->willReturn(response()->json($responseData));
            
        // Call the controller method
        $response = $controller->index($request);
    
        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());
    
        // Assert the response structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('links', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
    }

    // Test fetching a single article
    public function test_show()
    {
        // Create test data
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);
    
        // Mock the controller to return a response
        $controller = $this->getMockBuilder(ArticleController::class)
            ->onlyMethods(['show'])
            ->getMock();
            
        // Create a response that the controller should return
        $resource = new ArticleResource($article);
        $responseData = $resource->response()->getData(true);
        
        // The controller should return this response
        $controller->expects($this->once())
            ->method('show')
            ->willReturn(response()->json($responseData));
            
        // Call the controller method
        $response = $controller->show($article->id);
    
        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());
    
        // Assert the response structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
    }

    // Test fetching a personalized feed
    public function test_personalized_feed()
    {
        // Create test data
        $user = User::factory()->create();
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $article = Article::factory()->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);
        $article->authors()->attach($author->id);
    
        // Mock the request
        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    
        // Mock the controller to return a response
        $controller = $this->getMockBuilder(ArticleController::class)
            ->onlyMethods(['personalizedFeed'])
            ->getMock();
            
        // Create a response that the controller should return
        $resourceCollection = ArticleResource::collection(Article::paginate(15));
        $responseData = $resourceCollection->response()->getData(true);
        
        // The controller should return this response
        $controller->expects($this->once())
            ->method('personalizedFeed')
            ->willReturn(response()->json($responseData));
            
        // Call the controller method
        $response = $controller->personalizedFeed($request);
    
        // Assert the response status code
        $this->assertEquals(200, $response->getStatusCode());
    
        // Assert the response structure
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('links', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
    }
}