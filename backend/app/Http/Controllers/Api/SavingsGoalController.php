<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SavingsGoalController extends Controller
{
    /**
     * Get all savings goals for the authenticated user.
     */
    public function index(Request $request)
    {
        $goals = SavingsGoal::forUser($request->user()->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Savings goals retrieved successfully',
            'data' => $goals->items(),
            'pagination' => [
                'total' => $goals->total(),
                'per_page' => $goals->perPage(),
                'current_page' => $goals->currentPage(),
                'last_page' => $goals->lastPage()
            ]
        ]);
    }

    /**
     * Create a new savings goal.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'target_frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'target_amount_per_frequency' => 'required|numeric|min:1',
            'target_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $goal = SavingsGoal::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'target_amount' => $request->target_amount,
            'current_amount' => 0,
            'status' => 'active',
            'target_frequency' => $request->target_frequency,
            'target_amount_per_frequency' => $request->target_amount_per_frequency,
            'started_at' => now(),
            'target_date' => $request->target_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Savings goal created successfully',
            'data' => $goal
        ], 201);
    }

    /**
     * Get a specific savings goal detail.
     */
    public function show(Request $request, $id)
    {
        $goal = SavingsGoal::with('category')->find($id);

        if (!$goal || $goal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Savings goal not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Savings goal detail',
            'data' => $goal
        ]);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(Request $request, $id)
    {
        $goal = SavingsGoal::find($id);

        if (!$goal || $goal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Savings goal not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'sometimes|required|numeric|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'target_frequency' => ['sometimes', 'required', Rule::in(['daily', 'weekly', 'monthly'])],
            'target_amount_per_frequency' => 'sometimes|required|numeric|min:1',
            'target_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $goal->update($request->only([
            'name', 'description', 'target_amount', 'category_id', 
            'target_frequency', 'target_amount_per_frequency', 'target_date'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Savings goal updated successfully',
            'data' => $goal
        ]);
    }

    /**
     * Delete a savings goal.
     */
    public function destroy(Request $request, $id)
    {
        $goal = SavingsGoal::find($id);

        if (!$goal || $goal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Savings goal not found'
            ], 404);
        }

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Savings goal deleted successfully'
        ]);
    }

    /**
     * Update status of a savings goal.
     */
    public function updateStatus(Request $request, $id)
    {
        $goal = SavingsGoal::find($id);

        if (!$goal || $goal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Savings goal not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['active', 'paused', 'completed', 'failed'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate status change
        if ($goal->status === 'completed' && $request->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot activate a completed goal'
            ], 400);
        }

        $goal->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Savings goal status updated successfully',
            'data' => $goal
        ]);
    }

    /**
     * Get progress of a savings goal.
     */
    public function getProgress(Request $request, $id)
    {
        $goal = SavingsGoal::with(['transactions' => function ($query) {
            $query->orderBy('transaction_date', 'desc')->take(10);
        }])->find($id);

        if (!$goal || $goal->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Savings goal not found'
            ], 404);
        }

        // Calculate estimated completion date
        $estimatedDate = null;
        if ($goal->target_amount_per_frequency > 0 && $goal->remaining_amount > 0) {
            $periodsNeeded = ceil($goal->remaining_amount / $goal->target_amount_per_frequency);
            $estimatedDate = now();
            
            if ($goal->target_frequency === 'daily') {
                $estimatedDate->addDays($periodsNeeded);
            } elseif ($goal->target_frequency === 'weekly') {
                $estimatedDate->addWeeks($periodsNeeded);
            } elseif ($goal->target_frequency === 'monthly') {
                $estimatedDate->addMonths($periodsNeeded);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Savings goal progress retrieved',
            'data' => [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'percentage_completed' => $goal->progress_percentage,
                'amount_remaining' => $goal->remaining_amount,
                'estimated_completion_date' => $estimatedDate ? $estimatedDate->format('Y-m-d') : null,
                'target_frequency' => $goal->target_frequency,
                'target_amount_per_frequency' => $goal->target_amount_per_frequency,
                'status' => $goal->status,
                'transactions' => $goal->transactions
            ]
        ]);
    }
}
