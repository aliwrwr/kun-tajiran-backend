<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get wallet details and recent transactions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load(['wallet.transactions' => function ($q) {
            $q->latest()->take(30);
        }]);

        $wallet = $user->wallet;

        return response()->json([
            'wallet' => [
                'balance'          => $wallet?->balance ?? 0,
                'pending_balance'  => $wallet?->pending_balance ?? 0,
                'total_earned'     => $wallet?->total_earned ?? 0,
                'total_withdrawn'  => $wallet?->total_withdrawn ?? 0,
            ],
            'transactions' => $wallet?->transactions->map(fn ($t) => [
                'id'          => $t->id,
                'type'        => $t->type,
                'category'    => $t->category,
                'amount'      => $t->amount,
                'description' => $t->description,
                'created_at'  => $t->created_at->toDateTimeString(),
            ]) ?? [],
        ]);
    }

    /**
     * Request a withdrawal
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'         => 'required|integer|min:5000',
            'method'         => 'required|in:zain_cash,asia_hawala,bank_transfer,office_pickup',
            'account_number' => 'required_unless:method,office_pickup|string|max:50',
            'account_name'   => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json(['message' => 'رصيدك غير كافٍ لهذا السحب'], 400);
        }

        // Check no pending withdrawal exists
        $hasPending = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json(['message' => 'لديك طلب سحب قيد المعالجة, يرجى الانتظار'], 400);
        }

        WithdrawalRequest::create([
            'user_id'        => $user->id,
            'amount'         => $request->amount,
            'method'         => $request->method,
            'account_number' => $request->account_number,
            'account_name'   => $request->account_name,
            'status'         => 'pending',
        ]);

        return response()->json(['message' => 'تم إرسال طلب السحب. سيتم معالجته خلال 24 ساعة']);
    }

    /**
     * List withdrawal requests history
     */
    public function withdrawalHistory(Request $request): JsonResponse
    {
        $withdrawals = WithdrawalRequest::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'withdrawals' => $withdrawals->map(fn ($w) => [
                'id'           => $w->id,
                'amount'       => $w->amount,
                'method'       => $w->method_label,
                'account'      => $w->account_number,
                'status'       => $w->status,
                'created_at'   => $w->created_at->toDateTimeString(),
                'processed_at' => $w->processed_at?->toDateTimeString(),
                'admin_notes'  => $w->admin_notes,
            ]),
        ]);
    }
}
