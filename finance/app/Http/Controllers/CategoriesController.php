<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);
        $category = $request->user()->categories()->create($data);
        return response()->json($category, 201);
    }

    public function get(Request $request) {
        $categories = $request->user()->categories()->latest()->get();
        return response()->json($categories);
    }

    public function update(Request $request, $id) {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);
        $category = $request->user()->categories()->findOrFail($id);
        $category->update($data);
        return response()->json($category);
    }

    public function delete(Request $request, $id) {
        $category = $request->user()->categories()->findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
