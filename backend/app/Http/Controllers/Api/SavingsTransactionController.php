<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SavingsTransaction; // Pastikan Model ini sudah ada
use App\Models\SavingsGoal;        // Pastikan Model ini sudah ada

class SavingsTransactionController extends Controller
{
    public function store(Request $request, $goalId)
    {
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:deposit,withdraw',
            'note' => 'nullable|string'
        ]);

        // 2. Simpan transaksi ke database
        $transaction = new SavingsTransaction();
        $transaction->savings_goal_id = $goalId;
        $transaction->amount = $validated['amount'];
        $transaction->type = $validated['type'];
        $transaction->note = $validated['note'] ?? null;
        $transaction->save();

        // 3. Beri respon sukses
        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dicatat!',
            'data' => $transaction
        ], 201);
    }
}