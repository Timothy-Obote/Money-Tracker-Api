<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        // Custom validation with explicit negative check
        $validator = validator($request->all(), [
            'wallet_id'   => 'required|exists:wallets,id',
            'amount'      => 'required|numeric',
            'type'        => 'required|in:income,expense',
            'description' => 'nullable|string|max:255'
        ]);

        // Additional manual check for positive amount
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = $request->amount;
        if ($amount <= 0) {
            return response()->json([
                'errors' => [
                    'amount' => ['The amount must be greater than zero.']
                ]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::lockForUpdate()->findOrFail($request->wallet_id);
            
            $transaction = Transaction::create([
                'wallet_id'   => $request->wallet_id,
                'amount'      => $amount,
                'type'        => $request->type,
                'description' => $request->description
            ]);

            if ($request->type === 'income') {
                $wallet->balance += $amount;
            } else {
                $wallet->balance -= $amount;
            }
            $wallet->save();

            DB::commit();
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }
}