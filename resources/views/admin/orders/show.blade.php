@extends('admin.layouts.app')
@section('title', 'تفاصيل الطلب ' . $order->order_number)
@section('page-title', 'تفاصيل الطلب')

@section('content')
<div class="row g-4">

    <!-- Order Info + Items -->
    <div class="col-md-8">
        <div class="card table-card p-4 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-0">{{ $order->order_number }}</h5>
                    <span class="text-muted small">{{ $order->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <span class="badge bg-{{ $order->status_color }} fs-6 px-3 py-2">{{ $order->status_label }}</span>
            </div>

            <!-- Customer -->
            <div class="row g-2 mb-4">
                <div class="col-12"><div class="fw-bold text-muted small mb-1">معلومات الزبون</div></div>
                <div class="col-md-4">
                    <div class="bg-light rounded p-2">
                        <div class="text-muted small">الاسم</div>
                        <div class="fw-bold">{{ $order->customer_name }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded p-2">
                        <div class="text-muted small">الهاتف</div>
                        <div class="fw-bold">{{ $order->customer_phone }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light rounded p-2">
                        <div class="text-muted small">المدينة</div>
                        <div class="fw-bold">{{ $order->customer_city }}</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="bg-light rounded p-2">
                        <div class="text-muted small">العنوان</div>
                        <div class="fw-bold">{{ $order->customer_address }}</div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="fw-bold mb-2">المنتجات</div>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-end">سعر البيع</th>
                        <th class="text-end">ربح البائع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->total_sale) }} د.ع</td>
                        <td class="text-end text-success">{{ number_format($item->total_profit) }} د.ع</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">الإجمالي</td>
                        <td class="text-end">{{ number_format($order->total_sale_price) }} د.ع</td>
                        <td class="text-end text-success">{{ number_format($order->reseller_profit) }} د.ع</td>
                    </tr>
                </tfoot>
            </table>

            @if($order->notes)
            <div class="alert alert-light border mt-2">
                <strong>ملاحظات:</strong> {{ $order->notes }}
            </div>
            @endif
        </div>
    </div>

    <!-- Actions + Summary -->
    <div class="col-md-4">

        <!-- Financial Summary -->
        <div class="card table-card p-3 mb-3">
            <div class="fw-bold mb-3">📊 الملخص المالي</div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">إجمالي المبيعات</span>
                <span class="fw-bold">{{ number_format($order->total_sale_price) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">تكلفة الجملة</span>
                <span class="text-danger">- {{ number_format($order->total_wholesale_price) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">رسوم التوصيل</span>
                <span class="text-warning">- {{ number_format($order->delivery_fee) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">ربح البائع</span>
                <span class="text-success fw-bold">{{ number_format($order->reseller_profit) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <span class="fw-bold">ربح المنصة</span>
                <span class="fw-bold text-primary">{{ number_format($order->platform_profit) }} د.ع</span>
            </div>
        </div>

        <!-- Reseller Info -->
        <div class="card table-card p-3 mb-3">
            <div class="fw-bold mb-2">👤 البائع</div>
            <div>{{ $order->reseller?->name }}</div>
            <div class="text-muted small">{{ $order->reseller?->phone }}</div>
            <a href="{{ route('admin.users.show', $order->reseller_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                عرض ملف البائع
            </a>
        </div>

        <!-- Delivery Agent -->
        @if($order->deliveryAgent)
        <div class="card table-card p-3 mb-3">
            <div class="fw-bold mb-2">🚚 مندوب التوصيل</div>
            <div>{{ $order->deliveryAgent->user?->name }}</div>
            <div class="text-muted small">{{ $order->deliveryAgent->user?->phone }}</div>
        </div>
        @endif

        <!-- Update Status -->
        @if(!in_array($order->status, ['delivered', 'rejected', 'returned']))
        <div class="card table-card p-3 mb-3">
            <div class="fw-bold mb-3">تحديث الحالة</div>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf
                <select name="status" class="form-select mb-2">
                    @foreach(\App\Models\Order::STATUS_LABELS as $key => $label)
                        @if($key !== $order->status && !in_array($key, ['new']))
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endif
                    @endforeach
                </select>

                <select name="delivery_agent_id" class="form-select mb-2">
                    <option value="">-- اختر مندوب --</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected($order->delivery_agent_id == $agent->id)>
                            {{ $agent->user?->name }} - {{ $agent->city }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="rejection_reason" class="form-control mb-2"
                       placeholder="سبب الرفض (إن وجد)">

                <button class="btn btn-primary w-100">تحديث</button>
            </form>
        </div>
        @endif

    </div>
</div>

<a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-right me-1"></i> رجوع للطلبات
</a>
@endsection
