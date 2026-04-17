@extends('admin.layouts.app')

@section('title', 'تفاصيل المندوب')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">المندوب: {{ $agent->user->name }}</h4>
    <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body p-4 text-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;font-size:2rem;font-weight:900;">
                    {{ mb_substr($agent->user->name, 0, 1) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $agent->user->name }}</h5>
                <p class="text-muted mb-2">{{ $agent->user->phone }}</p>
                @if($agent->city)
                    <span class="badge bg-secondary mb-2">{{ $agent->city }}</span>
                @endif
                <div>
                    @php $sc = ['available'=>'success','busy'=>'warning','offline'=>'secondary']; $sl = ['available'=>'متاح','busy'=>'مشغول','offline'=>'غير متاح']; @endphp
                    <span class="badge bg-{{ $sc[$agent->status] ?? 'secondary' }} fs-6 px-3 py-2">{{ $sl[$agent->status] ?? $agent->status }}</span>
                </div>
            </div>
        </div>

        <!-- Update Status -->
        <div class="card shadow-sm rounded-3">
            <div class="card-header fw-bold">تغيير الحالة</div>
            <div class="card-body p-3">
                <form action="{{ route('admin.delivery.status', $agent) }}" method="POST">
                    @csrf
                    <select name="status" class="form-select mb-3">
                        <option value="available" {{ $agent->status === 'available' ? 'selected' : '' }}>متاح</option>
                        <option value="busy" {{ $agent->status === 'busy' ? 'selected' : '' }}>مشغول</option>
                        <option value="offline" {{ $agent->status === 'offline' ? 'selected' : '' }}>غير متاح</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary w-100 btn-sm">حفظ</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <div class="card shadow-sm text-center p-3">
                    <div class="fs-2 fw-black text-primary">{{ $stats['total_orders'] }}</div>
                    <div class="text-muted small">إجمالي الطلبات</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card shadow-sm text-center p-3">
                    <div class="fs-2 fw-black text-success">{{ $stats['delivered_orders'] }}</div>
                    <div class="text-muted small">طلبات مُسلَّمة</div>
                </div>
            </div>
        </div>

        <!-- Active Orders -->
        <div class="card shadow-sm rounded-3">
            <div class="card-header fw-bold">الطلبات المُسندة</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>المحافظة</th>
                            <th>الهاتف</th>
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
                                <td>{{ $order->customer_phone }}</td>
                                <td>
                                    @php $c=['in_delivery'=>'info','delivered'=>'success']; $l=['in_delivery'=>'قيد التوصيل','delivered'=>'مُسلَّم']; @endphp
                                    <span class="badge bg-{{ $c[$order->status] ?? 'secondary' }}">{{ $l[$order->status] ?? $order->status }}</span>
                                </td>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">لا توجد طلبات مُسندة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
