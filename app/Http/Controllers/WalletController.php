<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new user
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'api_key' => Str::random(40),
        ]);

        // Log registration event
        Log::info('New user registered: ' . $user->email);

        return response()->json(['api_key' => $user->api_key], 200);
    }

    // Create a new wallet for a user
    public function createWallet(Request $request, $userId)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the user
        $user = User::findOrFail($userId);

        // Check if the user already has a wallet
        if (Wallet::where('user_id', $userId)->exists()) {
            return response()->json('You already have a wallet', 400);
        }

        // Create a new wallet for the user
        $wallet = new Wallet([
            'user_id' => $userId,
            'currency' => $request->currency,
            'amount' => 0
        ]);
        $wallet->save();

        // Log wallet creation event
        Log::info('New wallet created for user ID: ' . $userId);

        return response()->json($wallet, 200);
    }

    // Credit a wallet with a specified amount
    public function creditWallet(Request $request, $walletId)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the wallet
        $wallet = Wallet::findOrFail($walletId);

        try {
            DB::beginTransaction();

            // Update wallet balance
            $wallet->increment('amount', $request->amount);

            // Create transaction record
            $transaction = new Transaction([
                'type' => 'credit',
                'amount' => $request->amount,
                'reference' => Str::random(10),
            ]);
            $wallet->transactions()->save($transaction);

            DB::commit();

            // Log credit transaction event
            Log::info('Credit transaction successful for wallet ID: ' . $walletId);

            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Log transaction failure event
            Log::error('Credit transaction failed for wallet ID: ' . $walletId);

            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }

    // Debit a wallet with a specified amount
    public function debitWallet(Request $request, $walletId)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the wallet
        $wallet = Wallet::findOrFail($walletId);

        // Ensure the wallet has sufficient balance before proceeding with the debit
        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 422);
        }

        try {
            DB::beginTransaction();

            // Update wallet balance
            $wallet->decrement('amount', $request->amount);

            // Create transaction record
            $transaction = new Transaction([
                'type' => 'debit',
                'amount' => $request->amount,
                'reference' => uniqid(), // Unique reference based on timestamp and unique identifier
            ]);
            $wallet->transactions()->save($transaction);

            DB::commit();

            // Log debit transaction event
            Log::info('Debit transaction successful for wallet ID: ' . $walletId);

            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Log transaction failure event
            Log::error('Debit transaction failed for wallet ID: ' . $walletId);

            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }

    // Get transaction history for a wallet
    public function getTransactionHistory(Request $request, $walletId)
    {
        // Find the wallet
        $wallet = Wallet::findOrFail($walletId);

        // Get transaction history
        $transactions = $wallet->transactions()->paginate(10);

        return response()->json($transactions);
    }
}
