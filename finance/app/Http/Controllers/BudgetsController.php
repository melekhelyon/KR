<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;

class BudgetsController extends Controller
{
    public function get(Request $request) {
        $budgets = $request->user()->budgets()->with('category')->latest()->get();
        return response()->json($budgets);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'planned_amount' => 'required|numeric|min:0',
            'spent_amount'   => 'nullable|numeric|min:0',
            'period_start'   => 'required|date',
            'period_end'     => 'required|date|after:period_start',
        ]);

        $data['spent_amount'] = $data['spent_amount'] ?? 0;

        $budget = $request->user()->budgets()->create($data);
        return response()->json($budget->load('category'), 201);
    }

    public function update(Request $request, $id) {
        $budget = $request->user()->budgets()->findOrFail($id);

        $data = $request->validate([
            'planned_amount' => 'required|numeric|min:0',
            'spent_amount'   => 'required|numeric|min:0',
            'period_end'     => 'required|date|after:period_start',
        ]);

        $budget->update($data);
        return response()->json($budget->load('category'));
    }

    public function delete(Request $request, $id) {
        $budget = $request->user()->budgets()->findOrFail($id);
        $budget->delete();
        return response()->json(['message' => 'Бюджет удален']);
    }
}
