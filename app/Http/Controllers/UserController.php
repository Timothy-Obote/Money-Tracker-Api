<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with('wallets')->findOrFail($id);
        $totalBalance = $user->wallets->sum('balance');
        return response()->json([
            'user' => $user,
            'total_balance' => $totalBalance
        ]);
    }
}