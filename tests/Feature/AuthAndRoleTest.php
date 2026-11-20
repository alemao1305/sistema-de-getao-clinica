<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_active_user_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@clinicafabris.com.br',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::where('email', 'recepcao@clinicafabris.com.br')->first();
        $user->is_active = false;
        $user->save();

        $response = $this->post('/login', [
            'email' => 'recepcao@clinicafabris.com.br',
            'password' => 'recepcao123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::where('email', 'admin@clinicafabris.com.br')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Clínica Fabris');
        $response->assertSee('panorama operacional');
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'admin@clinicafabris.com.br')->first();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
