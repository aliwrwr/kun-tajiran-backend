<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $perPage        = in_array((int) $request->per_page, [5, 10, 50, 100, 500]) ? (int) $request->per_page : 25;
        $agents         = DeliveryAgent::with(['user', 'orders' => fn ($q) => $q->where('status', 'out_for_delivery')])->paginate($perPage)->withQueryString();
        $availableCount = DeliveryAgent::where('status', 'available')->count();
        $busyCount      = DeliveryAgent::where('status', 'busy')->count();
        $offlineCount   = DeliveryAgent::where('status', 'offline')->count();
        return view('admin.delivery.index', compact('agents', 'availableCount', 'busyCount', 'offlineCount'));
    }

    public function create()
    {
        return view('admin.delivery.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'phone'          => 'required|string|unique:users,phone',
            'password'       => 'required|string|min:6',
            'city'           => 'required|string',
            'vehicle_type'   => 'nullable|string',
            'license_number' => 'nullable|string',
        ]);

        $user = User::create([
            'name'           => $request->name,
            'phone'          => $request->phone,
            'password'       => Hash::make($request->password),
            'city'           => $request->city,
            'role'           => 'delivery',
            'status'         => 'active',
            'phone_verified' => true,
        ]);

        DeliveryAgent::create([
            'user_id'        => $user->id,
            'city'           => $request->city,
            'vehicle_type'   => $request->vehicle_type,
            'license_number' => $request->license_number,
        ]);

        Wallet::create(['user_id' => $user->id]);

        return redirect()->route('admin.delivery.index')
            ->with('success', 'تم إضافة مندوب التوصيل بنجاح');
    }

    public function show(DeliveryAgent $deliveryAgent)
    {
        $deliveryAgent->load(['user', 'orders' => fn ($q) => $q->latest()->take(20)]);
        $agent = $deliveryAgent;
        $stats = [
            'total_orders'    => $deliveryAgent->orders()->count(),
            'delivered_orders'=> $deliveryAgent->orders()->where('status', 'delivered')->count(),
        ];
        $orders = $deliveryAgent->orders()->with('reseller')->latest()->paginate(15);
        return view('admin.delivery.show', compact('deliveryAgent', 'agent', 'stats', 'orders'));
    }

    public function updateStatus(Request $request, DeliveryAgent $deliveryAgent)
    {
        $request->validate(['status' => 'required|in:available,busy,offline']);
        $deliveryAgent->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث حالة المندوب');
    }
}
