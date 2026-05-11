<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Create a new transaction
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|string|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $product = Product::find($validated['product_id']);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            // Check if stock is available
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'quantity' => ['Insufficient stock available'],
                    ],
                ], 400);
            }

            // Calculate total price
            $total_price = $product->price * $validated['quantity'];

            // Create transaction
            $transaction = Transaction::create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'user_id' => $validated['user_id'],
                'total_price' => $total_price,
            ]);

            // Update product stock
            $product->update([
                'stock' => $product->stock - $validated['quantity'],
            ]);

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => [
                    'id' => $transaction->id,
                    'product_id' => $transaction->product_id,
                    'quantity' => $transaction->quantity,
                    'user_id' => $transaction->user_id,
                    'total_price' => $transaction->total_price,
                    'created_at' => $transaction->created_at,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        }
    }
}
