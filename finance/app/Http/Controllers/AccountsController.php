<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'currency' => 'required',
            'balance' => 'required',
        ]);
        $account = $request->user()->accounts()->create($data);
        return response()->json($account, 201);
    }

    public function get(Request $request) {
        $account = $request->user()->accounts()->where('status', 'active')->latest()->get();
        return response()->json($account);
    }

    public function update(Request $request, $id) {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'currency' => 'required',
            'balance' => 'required',
        ]);
        $account = $request->user()->accounts()->findOrFail($id);
        $account->update($data);
        return response()->json($account);
    }

    public function delete(Request $request, $id) {
        $account = $request->user()->accounts()->where('status', 'active')->findOrFail($id);
        $account->update(['status' => 'deleted']);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
