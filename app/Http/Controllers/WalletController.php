<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'api_key' => Str::random(40),
        ]);

        return response()->json(['api_key' => $user->api_key], 200);
    }

    public function createWallet(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

        return response()->json($wallet, 200);
    }



    public function creditWallet(Request $request, $walletId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }


    public function debitWallet(Request $request, $walletId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $wallet = Wallet::findOrFail($walletId);

        // Ensure the wallet has sufficient balance before proceeding with the debit
        if ($wallet->balance > $request->amount) {
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

            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }


    public function getTransactionHistory(Request $request, $walletId)
    {
        $wallet = Wallet::findOrFail($walletId);
        $transactions = $wallet->transactions()->paginate(10);

        return response()->json($transactions);
    }
}
