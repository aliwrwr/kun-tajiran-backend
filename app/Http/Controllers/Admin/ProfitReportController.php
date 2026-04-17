<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'reseller')
            ->with('wallet')
            ->withCount('orders');

        // Filter by search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $resellers = $query->paginate(20)->withQueryString();

        // Totals
        $totals = Wallet::selectRaw('
            SUM(total_earned) as total_earned,
            SUM(total_withdrawn) as total_withdrawn,
            SUM(balance) as total_balance,
            SUM(pending_balance) as total_pending
        ')->first();

        // Cities for filter
        $cities = User::where('role', 'reseller')
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city');

        // Recent withdrawal requests (pending)
        $pendingWithdrawals = WithdrawalRequest::where('status', 'pending')
            ->with('user:id,name,phone')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.profits.index', compact(
            'resellers', 'totals', 'cities', 'pendingWithdrawals'
        ));
    }
}
