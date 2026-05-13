<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinancialRecordController extends Controller
{
    /**
     * Get all financial records
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = $request->user()->financialRecords();

            // Filter by type
            if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
                $query->where('type', $request->type);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }

            $records = $query->orderBy('transaction_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Financial records retrieved successfully',
                'data' => $records
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial records',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get financial records summary
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        try {
            $query = $request->user()->financialRecords();

            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }

            $totalIncome = $query->clone()->where('type', 'income')->sum('amount');
            $totalExpense = $query->clone()->where('type', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;

            return response()->json([
                'success' => true,
                'message' => 'Summary retrieved successfully',
                'data' => [
                    'total_income' => (string)$totalIncome,
                    'total_expense' => (string)$totalExpense,
                    'balance' => (string)$balance
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve summary',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Create a new financial record
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $record = $request->user()->financialRecords()->create([
                'title' => $request->title,
                'amount' => $request->amount,
                'type' => $request->type,
                'category' => $request->category,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Financial record created successfully',
                'data' => $record
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create financial record',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get a specific financial record
     * 
     * @param String $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        try {
            $record = $request->user()->financialRecords()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Financial record retrieved successfully',
                'data' => $record
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Financial record not found',
                'errors' => ['id' => ['The financial record not found.']]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial record',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update a financial record
     * 
     * @param String $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $record = $request->user()->financialRecords()->findOrFail($id);
            $record->update([
                'title' => $request->title,
                'amount' => $request->amount,
                'type' => $request->type,
                'category' => $request->category,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Financial record updated successfully',
                'data' => $record
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Financial record not found',
                'errors' => ['id' => ['The financial record not found.']]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update financial record',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Delete a financial record
     * 
     * @param String $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        try {
            $record = $request->user()->financialRecords()->findOrFail($id);
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Financial record deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Financial record not found',
                'errors' => ['id' => ['The financial record not found.']]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete financial record',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
