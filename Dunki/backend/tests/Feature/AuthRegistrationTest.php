<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => ' Test Worker ',
            'email' => ' TEST@EXAMPLE.COM ',
            'phone' => ' 01700000000 ',
            'password' => 'password123',
            'role' => 'worker',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'phone', 'role', 'tracking_id'], 'token']);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Worker',
            'email' => 'test@example.com',
            'phone' => '01700000000',
            'role' => 'worker',
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $this->postJson('/api/register', [
            'name' => 'First Worker',
            'email' => 'worker@example.com',
            'phone' => '01700000001',
            'password' => 'password123',
            'role' => 'worker',
        ])->assertCreated();

        $this->postJson('/api/register', [
            'name' => 'Second Worker',
            'email' => 'worker@example.com',
            'phone' => '01700000002',
            'password' => 'password123',
            'role' => 'worker',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_registration_rejects_short_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test Worker',
            'email' => 'worker2@example.com',
            'phone' => '01700000003',
            'password' => 'short',
            'role' => 'worker',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}
