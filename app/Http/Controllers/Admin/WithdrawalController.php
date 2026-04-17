<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /** @var FirestoreService */
    private $fs;

    public function __construct(FirestoreService $fs)
    {
        $this->fs = $fs;
    }

    public function index(Request $request)
    {
        $query = WithdrawalRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array((int) $request->per_page, [5, 10, 50, 100, 500]) ? (int) $request->per_page : 25;
        $withdrawals = $query->paginate($perPage)->withQueryString();
        $pendingTotal = WithdrawalRequest::where('status', 'pending')->sum('amount');

        return view('admin.withdrawals.index', compact('withdrawals', 'pendingTotal'));
    }

    public function processWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'action'      => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تم معالجته مسبقاً');
        }

        return DB::transaction(function () use ($request, $withdrawal) {
            if ($request->action === 'approved') {
                $wallet = Wallet::where('user_id', $withdrawal->user_id)->first();

                if (!$wallet || $wallet->balance < $withdrawal->amount) {
                    return back()->with('error', 'رصيد المستخدم غير كافٍ');
                }

                $wallet->debit(
                    $withdrawal->amount,
                    'withdrawal',
                    "سحب رصيد #{$withdrawal->id} عبر {$withdrawal->method_label}"
                );

                $withdrawal->update([
                    'status'       => 'processed',
                    'admin_notes'  => $request->admin_notes,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                // Sync wallet balance to Firestore
                $wallet->refresh();
                $this->fs->update('users', (string) $withdrawal->user_id, [
                    'wallet_balance'      => (int) $wallet->balance,
                    'wallet_total_earned' => (int) $wallet->total_earned,
                ]);

                return back()->with('success', 'تم تأكيد السحب وخصم المبلغ من المحفظة');
            } else {
                $withdrawal->update([
                    'status'       => 'rejected',
                    'admin_notes'  => $request->admin_notes,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);

                return back()->with('success', 'تم رفض طلب السحب');
            }
        });
    }
}
