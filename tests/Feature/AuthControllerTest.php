<?

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test user registration
    public function test_register()
    {
        $request = new Request([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $controller = new AuthController();
        $response = $controller->register($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('user', $response->getData(true));
        $this->assertArrayHasKey('token', $response->getData(true));
    }

    // Test user login
    public function test_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $request = new Request([
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $controller = new AuthController();
        $response = $controller->login($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('user', $response->getData(true));
        $this->assertArrayHasKey('token', $response->getData(true));
    }

    // Test user logout
    public function test_logout()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
    
        // Make a request to the logout endpoint with the token in the headers
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
    
        // Assert the response
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);
    
        // Ensure the token was deleted
        $this->assertCount(0, $user->tokens);
    }

    // Test forgot password
    public function test_forgot_password()
    {
        $request = new Request([
            'email' => 'john@example.com',
        ]);

        Password::shouldReceive('sendResetLink')
                ->once()
                ->andReturn(Password::RESET_LINK_SENT);

        $controller = new AuthController();
        $response = $controller->forgotPassword($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('message', $response->getData(true));
    }

    // Test reset password
    public function test_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $request = new Request([
            'token' => 'valid-token',
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        Password::shouldReceive('reset')
                ->once()
                ->andReturn(Password::PASSWORD_RESET);

        $controller = new AuthController();
        $response = $controller->resetPassword($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('message', $response->getData(true));
    }
}