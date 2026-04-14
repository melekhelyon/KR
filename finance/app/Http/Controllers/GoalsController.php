<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoalsController extends Controller
{
    public function get(Request $request)
    {
        $goals = $request->user()->goals()->latest()->get();
        return response()->json($goals);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'required|string',
            'target_amount'  => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'start_date'     => 'required|date',
            'target_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $goal = $request->user()->goals()->create($data);

        return response()->json($goal, 201);
    }

    public function update(Request $request, $id)
    {
        $goal = $request->user()->goals()->findOrFail($id);

        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'required|string',
            'target_amount'  => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'target_date'    => 'nullable|date|after_or_equal:start_date',
            'status'         => ['required', Rule::in(['in_progress', 'achieved', 'failed'])],
        ]);

        $goal->update($data);

        return response()->json($goal);
    }

    public function delete(Request $request, $id)
    {
        $goal = $request->user()->goals()->findOrFail($id);
        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully']);
    }

    public function updateProgress(Request $request, $id) 
    {
        $goal = $request->user()->goals()->findOrFail($id);
    
        if ($goal->status === 'failed') {
            return response()->json(['message' => 'Нельзя изменять прогресс проваленной цели'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $newAmount = $goal->current_amount + $request->amount;

        if ($newAmount > $goal->target_amount) $newAmount = $goal->target_amount;
        if ($newAmount < 0) $newAmount = 0;

        $status = ($newAmount >= $goal->target_amount) ? 'achieved' : 'in_progress';

        $goal->update([
            'current_amount' => $newAmount,
            'status' => $status
        ]);

        return response()->json($goal);
    }
}
