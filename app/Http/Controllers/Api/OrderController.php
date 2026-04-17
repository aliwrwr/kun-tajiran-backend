<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /** @var FirestoreService */
    private $fs;

    public function __construct(FirestoreService $fs)
    {
        $this->fs = $fs;
    }

    private const IRAQI_CITIES = [
        'بغداد', 'البصرة', 'الموصل', 'أربيل', 'كركوك', 'النجف', 'كربلاء',
        'السليمانية', 'الأنبار', 'بابل', 'ديالى', 'ذي قار', 'المثنى',
        'ميسان', 'نينوى', 'القادسية', 'صلاح الدين', 'واسط', 'دهوك', 'حلبجة',
    ];

    /**
     * List reseller's orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.product'])
            ->forReseller($request->user()->id)
            ->latest();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->paginate(15);

        return response()->json([
            'orders' => $orders->map(fn ($o) => $this->formatOrder($o)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * Place a new order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:20',
            'customer_city'    => 'required|string|max:50',
            'customer_address' => 'required|string|max:255',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:99',
            'items.*.sale_price' => 'required|integer|min:0',
            'notes'                  => 'nullable|string|max:500',
            'delivery_fee_override'  => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $reseller = $request->user();
            $orderItems = [];
            $totalSale = 0;
            $totalWholesale = 0;
            $totalProfit = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if (!$product || !$product->isInStock()) {
                    return response()->json([
                        'message' => "المنتج '{$product?->name_ar}' غير متوفر في المخزن",
                    ], 400);
                }

                if ($item['quantity'] > $product->stock_quantity) {
                    return response()->json([
                        'message' => "الكمية المطلوبة تتجاوز المخزون المتاح للمنتج '{$product->name_ar}'",
                    ], 400);
                }

                if ($item['sale_price'] < $product->min_price) {
                    return response()->json([
                        'message' => "سعر البيع لا يمكن أن يقل عن " . number_format($product->min_price) . " د.ع",
                    ], 400);
                }

                $orderItems[] = [
                    'product'  => $product,
                    'quantity' => $item['quantity'],
                    'sale_price' => $item['sale_price'],
                ];

                $totalSale      += $item['sale_price'] * $item['quantity'];
                $totalWholesale += $product->wholesale_price * $item['quantity'];
            }

            // Calculate profit per item (gross margin)
            foreach ($orderItems as &$item) {
                $profitPerItem = max(0, $item['sale_price'] - $item['product']->wholesale_price);
                $item['profit_per_item'] = $profitPerItem;
                $totalProfit += $profitPerItem * $item['quantity'];
            }
            unset($item);

            // Zone-based delivery fee (may be discounted by an active offer)
            $zoneFee = DeliveryZone::getFeeForReseller($request->customer_city, $reseller->id);

            // Reseller may reduce the delivery fee charged to the customer;
            // the shortfall (delivery subsidy) is deducted from their profit.
            $deliveryFee = $request->filled('delivery_fee_override')
                ? min($zoneFee, max(0, (int) $request->delivery_fee_override))
                : $zoneFee;

            $deliverySubsidy = $zoneFee - $deliveryFee; // reseller absorbs this

            $resellerProfit = max(0, $totalProfit - $deliverySubsidy);
            $platformProfit = max(0, $totalSale - $totalWholesale - $resellerProfit - $deliveryFee);

            // Create order
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'reseller_id'           => $reseller->id,
                'customer_name'         => $request->customer_name,
                'customer_phone'        => $request->customer_phone,
                'customer_city'         => $request->customer_city,
                'customer_address'      => $request->customer_address,
                'total_sale_price'      => $totalSale,
                'total_wholesale_price' => $totalWholesale,
                'delivery_fee'          => $deliveryFee,
                'zone_delivery_fee'     => $zoneFee,
                'reseller_profit'       => $resellerProfit,
                'platform_profit'       => $platformProfit,
                'status'                => 'new',
                'payment_method'        => 'cod',
                'notes'                 => $request->notes,
            ]);

            // Create order items and decrement stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item['product']->id,
                    'product_name'   => $item['product']->name_ar,
                    'quantity'       => $item['quantity'],
                    'wholesale_price' => $item['product']->wholesale_price,
                    'sale_price'     => $item['sale_price'],
                    'profit_per_item' => $item['profit_per_item'],
                ]);

                $item['product']->decrementStock($item['quantity']);
                $item['product']->increment('sales_count', $item['quantity']);

                // Sync stock changes to Firestore
                $fresh = $item['product']->fresh();
                $this->fs->update('products', (string) $item['product']->id, [
                    'stock_quantity' => (int) $fresh->stock_quantity,
                    'sales_count'   => (int) $fresh->sales_count,
                ]);
            }

            // Sync new order to Firestore for real-time Flutter updates
            $this->syncOrderToFirestore($order->load('items'));

            return response()->json([
                'message' => 'تم تسجيل الطلب بنجاح',
                'order'   => $this->formatOrder($order->load('items')),
            ], 201);
        });
    }

    /**
     * Get order details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with(['items.product', 'deliveryAgent.user'])
            ->forReseller($request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }

        return response()->json(['order' => $this->formatOrder($order, detailed: true)]);
    }

    /**
     * Returns list of Iraqi cities for city dropdown
     */
    public function cities(): JsonResponse
    {
        return response()->json(['cities' => self::IRAQI_CITIES]);
    }

    // --- Private helpers ---

    /**
     * Sync an order to Firestore for real-time status tracking in Flutter.
     */
    private function syncOrderToFirestore(Order $order): void
    {
        $items = $order->relationLoaded('items')
            ? $order->items->map(fn($i) => [
                'product_id'      => (string) $i->product_id,
                'product_name'    => $i->product_name,
                'quantity'        => (int) $i->quantity,
                'sale_price'      => (int) $i->sale_price,
                'wholesale_price' => (int) $i->wholesale_price,
                'profit_per_item' => (int) $i->profit_per_item,
            ])->values()->all()
            : [];

        $this->fs->set('orders', (string) $order->id, [
            'id'                    => (string) $order->id,
            'order_number'          => $order->order_number,
            'reseller_id'           => (string) $order->reseller_id,
            'customer_name'         => $order->customer_name,
            'customer_phone'        => $order->customer_phone,
            'customer_city'         => $order->customer_city,
            'customer_address'      => $order->customer_address,
            'status'                => $order->status,
            'payment_method'        => $order->payment_method ?? 'cod',
            'total_sale_price'      => (int) $order->total_sale_price,
            'total_wholesale_price' => (int) $order->total_wholesale_price,
            'delivery_fee'          => (int) $order->delivery_fee,
            'zone_delivery_fee'     => (int) $order->zone_delivery_fee,
            'reseller_profit'       => (int) $order->reseller_profit,
            'platform_profit'       => (int) $order->platform_profit,
            'notes'                 => $order->notes,
            'rejection_reason'      => $order->rejection_reason,
            'delivered_at'          => $order->delivered_at?->toDateTimeString(),
            'created_at'            => $order->created_at->toDateTimeString(),
            'updated_at'            => now()->toDateTimeString(),
            'items'                 => $items,
        ]);
    }

    private function formatOrder(Order $order, bool $detailed = false): array
    {
        $data = [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'customer_name'  => $order->customer_name,
            'customer_city'  => $order->customer_city,
            'status'         => $order->status,
            'status_label'   => $order->status_label,
            'status_color'   => $order->status_color,
            'total_sale'     => $order->total_sale_price,
            'delivery_fee'      => $order->delivery_fee,
            'zone_delivery_fee' => $order->zone_delivery_fee,
            'my_profit'         => $order->reseller_profit,
            'created_at'     => $order->created_at->toDateTimeString(),
        ];

        if ($detailed) {
            $data['customer_phone']   = $order->customer_phone;
            $data['customer_address'] = $order->customer_address;
            $data['notes']            = $order->notes;
            $data['payment_method']   = $order->payment_method;
            $data['delivered_at']     = $order->delivered_at?->toDateTimeString();
            $data['rejection_reason'] = $order->rejection_reason;
            $data['items']            = $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'sale_price'   => $item->sale_price,
                'profit'       => $item->total_profit,
            ]);
        }

        return $data;
    }
}
