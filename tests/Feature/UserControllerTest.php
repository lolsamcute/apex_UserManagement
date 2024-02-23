<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all users.
     *
     * @return void
     */
    public function testFetchAllUsers()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'users');
    }

    /**
     * Test creating a new user.
     *
     * @return void
     */
    public function testCreateUser()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'roles' => 'user',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user']);
    }

    // Add more tests for show, update, and destroy methods as needed
}
