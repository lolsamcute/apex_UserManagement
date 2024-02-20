<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['api_key']);
    }

    public function testCreateWallet()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/users/{$user->id}/wallets", [
            'currency' => 'USD',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['id', 'currency', 'user_id', 'created_at', 'updated_at']);
    }

    public function testCreditWallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/credit", [
            'amount' => 100,
            'reference' => 'txn123',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['id', 'wallet_id', 'type', 'amount', 'reference', 'created_at', 'updated_at']);
    }

    public function testDebitWallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $wallet->transactions()->create(['type' => 'credit', 'amount' => 200, 'reference' => 'txn456']);

        $response = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/debit", [
            'amount' => 100,
            'reference' => 'txn789',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['id', 'wallet_id', 'type', 'amount', 'reference', 'created_at', 'updated_at']);
    }

    public function testGetTransactionHistory()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $transactions = Transaction::factory(5)->create(['wallet_id' => $wallet->id]);

        $response = $this->actingAs($user)->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'wallet_id',
                            'type',
                            'amount',
                            'reference',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ]);
    }
}
