<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\Wallet;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private FirestoreService $fs) {}
    public function index(Request $request)
    {
        $query = Order::with(['reseller', 'deliveryAgent.user'])->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_phone', 'like', "%{$term}%");
            });
        }
        if ($request->filled('city')) {
            $query->where('customer_city', $request->city);
        }

        $perPage = in_array((int) $request->per_page, [5, 10, 50, 100, 500]) ? (int) $request->per_page : 25;
        $orders = $query->paginate($perPage)->withQueryString();
        $agents = DeliveryAgent::with('user')->where('status', 'available')->get();

        return view('admin.orders.index', compact('orders', 'agents'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'reseller', 'deliveryAgent.user']);
        $agents = DeliveryAgent::with('user')->get();
        return view('admin.orders.show', compact('order', 'agents'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'           => 'required|in:confirmed,preparing,out_for_delivery,delivered,rejected,returned',
            'delivery_agent_id'=> 'nullable|exists:delivery_agents,id',
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $order) {
            $previousStatus = $order->status;
            $newStatus      = $request->status;
            $updateData     = ['status' => $newStatus];

            // Guard: don't process if status didn't change
            if ($previousStatus === $newStatus) {
                return back()->with('info', 'الحالة لم تتغير.');
            }

            if ($newStatus === 'out_for_delivery' && $request->delivery_agent_id) {
                $updateData['delivery_agent_id'] = $request->delivery_agent_id;
            }

            if ($newStatus === 'delivered') {
                $updateData['delivered_at']    = now();
                $updateData['payment_status']  = 'paid';

                // Credit the reseller's wallet
                $this->creditResellerProfit($order);

                // Update delivery agent stats
                if ($order->deliveryAgent) {
                    $order->deliveryAgent->increment('delivered_count');
                    $order->deliveryAgent->update(['status' => 'available']);
                }
            }

            // Return stock only if coming FROM a stock-deducted state
            // Stock is deducted when order is placed (status: new).
            // If previous status was already rejected/returned, stock was already restored — skip.
            $stockRestoredStatuses = ['rejected', 'returned'];
            $isMovingToCancel      = in_array($newStatus, $stockRestoredStatuses);
            $wasAlreadyCancelled   = in_array($previousStatus, $stockRestoredStatuses);

            if ($isMovingToCancel && !$wasAlreadyCancelled) {
                $updateData['rejected_at']      = now();
                $updateData['rejection_reason'] = $request->rejection_reason;

                // Reload items with product to ensure fresh data
                $order->load('items.product');
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->incrementStock($item->quantity);
                        // Sync restored stock to Firestore
                        $this->fs->update('products', (string) $item->product->id, [
                            'stock_quantity' => (int) $item->product->fresh()->stock_quantity,
                        ]);
                    }
                }
            }

            $order->update($updateData);

            // Sync updated order status to Firestore for real-time Flutter tracking
            $this->fs->update('orders', (string) $order->id, [
                'status'           => $newStatus,
                'rejection_reason' => $updateData['rejection_reason'] ?? $order->rejection_reason,
                'delivered_at'     => $updateData['delivered_at'] ?? $order->delivered_at?->toDateTimeString(),
                'updated_at'       => now()->toDateTimeString(),
            ]);

            return back()->with('success', 'تم تحديث حالة الطلب إلى: ' . $order->fresh()->status_label);
        });
    }

    public function assignDelivery(Request $request, Order $order)
    {
        $request->validate([
            'delivery_agent_id' => 'required|exists:delivery_agents,id',
        ]);

        $order->update([
            'delivery_agent_id' => $request->delivery_agent_id,
            'status' => 'out_for_delivery',
        ]);

        $this->fs->update('orders', (string) $order->id, [
            'status'     => 'out_for_delivery',
            'updated_at' => now()->toDateTimeString(),
        ]);

        DeliveryAgent::find($request->delivery_agent_id)
            ->update(['status' => 'busy']);

        return back()->with('success', 'تم تعيين المندوب للطلب');
    }

    private function creditResellerProfit(Order $order): void
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $order->reseller_id],
            ['balance' => 0, 'total_earned' => 0]
        );

        $wallet->credit(
            amount: $order->reseller_profit,
            category: 'order_profit',
            description: "ربح الطلب رقم {$order->order_number}",
            orderId: $order->id
        );
    }

    public function destroy(Order $order)
    {
        $this->fs->delete('orders', (string) $order->id);
        $order->delete();
        return back()->with('success', 'تم حذف الطلب #' . $order->order_number);
    }

    public function printReceipt(Order $order)
    {
        $order->load(['items.product']);
        return view('admin.orders.print', compact('order'));
    }
}
