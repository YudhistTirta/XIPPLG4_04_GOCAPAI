<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\Auth;

class SavingsTransactionController extends Controller
{
    public function store(Request $request, $goalId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:deposit,withdrawal',
            'description' => 'nullable|string' // Ganti dari note ke description
        ]);

        $transaction = new SavingsTransaction();
        $transaction->user_id = Auth::id(); // Mengambil ID user yang login
        $transaction->savings_goal_id = $goalId;
        $transaction->amount = $validated['amount'];
        $transaction->type = $validated['type'];
        $transaction->description = $validated['description']; // Ganti ke description
        $transaction->transaction_date = now();
        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil!',
            'data' => $transaction
        ], 201);
    }
}