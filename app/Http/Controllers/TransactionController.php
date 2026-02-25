<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet_id'   => 'required|exists:wallets,id',
            'amount'      => 'required|numeric|min:0.01',
            'type'        => 'required|in:income,expense',
            'description' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $wallet = Wallet::lockForUpdate()->findOrFail($validated['wallet_id']);
            $transaction = Transaction::create($validated);
            if ($validated['type'] === 'income') {
                $wallet->balance += $validated['amount'];
            } else {
                $wallet->balance -= $validated['amount'];
            }
            $wallet->save();
            DB::commit();
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }
}