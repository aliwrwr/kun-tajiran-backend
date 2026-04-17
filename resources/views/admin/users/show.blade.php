@extends('admin.layouts.app')

@section('title', 'تفاصيل المسوق')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">تفاصيل المسوق: {{ $user->name }}</h4>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة
    </a>
</div>

<div class="row g-4">
    <!-- Profile -->
    <div class="col-lg-4">
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body p-4 text-center">
                <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;font-size:2rem;font-weight:900;">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ $user->phone }}</p>
                @if($user->city)
                    <span class="badge bg-secondary">{{ $user->city }}</span>
                @endif
                <div class="mt-3">
                    @if($user->status === 'active')
                        <span class="badge bg-success fs-6 px-3 py-2">نشط</span>
                    @else
                        <span class="badge bg-danger fs-6 px-3 py-2">محظور</span>
                    @endif
                </div>
                <div class="mt-3">
                    <small class="text-muted">انضم في {{ $user->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Wallet -->
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-header fw-bold">المحفظة</div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">الرصيد الحالي</span>
                    <strong class="text-success fs-5">{{ number_format($user->wallet->balance ?? 0) }} د.ع</strong>
                </div>
                <hr>
                <form action="{{ route('admin.users.balance', $user) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold">نوع العملية</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="bonus">مكافأة (إضافة)</option>
                            <option value="penalty">خصم (حسم)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" name="amount" class="form-control form-control-sm" placeholder="المبلغ (د.ع)" min="1" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="ملاحظة (اختياري)">
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">تعديل الرصيد</button>
                </form>
            </div>
        </div>

        <!-- Toggle Status -->
        <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
            @csrf
            <button type="submit" class="btn w-100 fw-bold {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-success' }}"
                    onclick="return confirm('هل أنت متأكد؟')">
                {{ $user->status === 'active' ? '🚫 حظر المسوق' : '✅ تفعيل المسوق' }}
            </button>
        </form>
    </div>

    <!-- Stats + Orders -->
    <div class="col-lg-8">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card shadow-sm text-center p-3">
                    <div class="fs-3 fw-black text-primary">{{ $stats['total_orders'] }}</div>
                    <div class="text-muted small mt-1">إجمالي الطلبات</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm text-center p-3">
                    <div class="fs-3 fw-black text-success">{{ number_format($stats['total_profit']) }}</div>
                    <div class="text-muted small mt-1">إجمالي الأرباح (د.ع)</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm text-center p-3">
                    <div class="fs-3 fw-black text-info">{{ $stats['delivered_orders'] }}</div>
                    <div class="text-muted small mt-1">طلبات مُسلَّمة</div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card shadow-sm rounded-3">
            <div class="card-header fw-bold">آخر الطلبات</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>المحافظة</th>
                            <th>سعر البيع</th>
                            <th>الربح</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="fw-bold">{{ $order->order_number }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->city }}</td>
                                <td>{{ number_format($order->sale_price) }}</td>
                                <td class="text-success fw-bold">{{ number_format($order->reseller_profit) }}</td>
                                <td>
                                    @php
                                        $colors = ['pending'=>'warning','confirmed'=>'primary','in_delivery'=>'info','delivered'=>'success','cancelled'=>'secondary','rejected'=>'danger','returned'=>'danger'];
                                        $labels = ['pending'=>'جديد','confirmed'=>'مؤكد','in_delivery'=>'توصيل','delivered'=>'مُسلَّم','cancelled'=>'ملغي','rejected'=>'مرفوض','returned'=>'مرتجع'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$order->status] ?? 'secondary' }}">{{ $labels[$order->status] ?? $order->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">لا توجد طلبات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="card-footer">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
