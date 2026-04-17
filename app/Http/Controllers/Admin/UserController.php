<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** @var FirestoreService */
    private $fs;

    public function __construct(FirestoreService $fs)
    {
        $this->fs = $fs;
    }

    /** Sync user data to Firestore users/{id} */
    private function syncUserToFirestore(User $user): void
    {
        $user->loadMissing('wallet');
        $wallet = $user->wallet;
        $this->fs->set('users', (string) $user->id, [
            'id'                  => (string) $user->id,
            'name'                => $user->name,
            'phone'               => $user->phone,
            'city'                => $user->city ?? '',
            'role'                => $user->role,
            'status'              => $user->status,
            'phone_verified'      => (bool) $user->phone_verified,
            'fcm_token'           => $user->fcm_token ?? null,
            'avatar'              => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'wallet_balance'      => (int) ($wallet ? $wallet->balance : 0),
            'wallet_total_earned' => (int) ($wallet ? $wallet->total_earned : 0),
            'created_at'          => $user->created_at ? $user->created_at->toIso8601String() : '',
        ]);
    }

    public function index(Request $request)
    {
        $query = User::with('wallet')
            ->where('role', 'reseller')
            ->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $perPage = in_array((int) $request->per_page, [5, 10, 50, 100, 500]) ? (int) $request->per_page : 25;
        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['wallet.transactions']);
        $orders = $user->orders()->latest()->paginate(10);

        $stats = [
            'total_orders'    => $user->orders()->count(),
            'delivered_orders'=> $user->orders()->where('status', 'delivered')->count(),
            'total_profit'    => $user->wallet ? $user->wallet->total_earned : 0,
        ];

        return view('admin.users.show', compact('user', 'orders', 'stats'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'لا يمكن تغيير حالة المسؤول');
        }

        $newStatus = $user->status === 'active' ? 'banned' : 'active';
        $user->update(['status' => $newStatus]);

        // Sync status to Firestore
        $this->fs->update('users', (string) $user->id, ['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'تم تفعيل الحساب' : 'تم حظر الحساب';
        return back()->with('success', $msg);
    }

    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount'      => 'required|integer',
            'description' => 'required|string|max:255',
        ]);

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'total_earned' => 0]
        );

        $amount = abs($request->amount);

        if ($request->amount > 0) {
            $wallet->credit($amount, 'bonus', $request->description);
        } else {
            if ($wallet->balance < $amount) {
                return back()->with('error', 'رصيد المستخدم غير كافٍ');
            }
            $wallet->debit($amount, 'penalty', $request->description);
        }

        // Sync wallet balance to Firestore
        $wallet->refresh();
        $this->fs->update('users', (string) $user->id, [
            'wallet_balance'      => (int) $wallet->balance,
            'wallet_total_earned' => (int) $wallet->total_earned,
        ]);

        return back()->with('success', 'تم تعديل الرصيد');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'لا يمكن حذف المسؤول');
        }
        // Delete from Firestore first
        $this->fs->delete('users', (string) $user->id);
        $user->delete();
        return back()->with('success', 'تم حذف البائع ' . $user->name);
    }
}
