<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'doctor@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->user->assignRole('doctor');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'doctor@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'roles', 'permissions'],
                'token',
            ])
            ->assertJsonPath('user.email', 'doctor@example.com')
            ->assertJsonPath('user.roles.0', 'doctor');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Inicio de sesión exitoso',
            'causer_id' => $this->user->id,
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'doctor@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Dra. Claudia Ramos',
            'email' => 'claudia@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJsonPath('user.email', 'claudia@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'claudia@example.com',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Usuario registrado',
        ]);
    }

    public function test_register_validation_fails_on_duplicate_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Otro Doctor',
            'email' => 'doctor@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_check_status_and_renew_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/check-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada correctamente',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Cierre de sesión',
            'causer_id' => $this->user->id,
        ]);
    }
}
