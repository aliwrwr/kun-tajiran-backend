@extends('admin.layouts.app')
@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@push('styles')
<style>
.chart-container { position: relative; height: 280px; }
</style>
@endpush

@section('content')

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-bag-fill text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">طلبات اليوم</div>
                    <div class="fw-bold fs-4">{{ number_format($stats['orders_today']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-success bg-opacity-10">
                    <i class="bi bi-currency-dollar text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">أرباح اليوم</div>
                    <div class="fw-bold fs-4">{{ number_format($stats['profit_today']) }}</div>
                    <div class="text-muted" style="font-size:.7rem">دينار عراقي</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-warning bg-opacity-10">
                    <i class="bi bi-clock-fill text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">قيد التوصيل</div>
                    <div class="fw-bold fs-4">{{ number_format($stats['out_for_delivery']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-info bg-opacity-10">
                    <i class="bi bi-people-fill text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">البائعون النشطون</div>
                    <div class="fw-bold fs-4">{{ number_format($stats['active_resellers']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 border-start border-warning border-3">
            <div class="text-muted small mb-1">طلبات جديدة (تحتاج مراجعة)</div>
            <div class="fw-bold fs-3 text-warning">{{ $stats['pending_orders'] }}</div>
            <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="btn btn-sm btn-warning mt-2">
                عرض الطلبات الجديدة <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 border-start border-danger border-3">
            <div class="text-muted small mb-1">طلبات سحب معلقة</div>
            <div class="fw-bold fs-3 text-danger">{{ $stats['pending_withdrawals'] }}</div>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="btn btn-sm btn-danger mt-2">
                معالجة السحوبات <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 border-start border-primary border-3">
            <div class="text-muted small mb-1">إجمالي الطلبات</div>
            <div class="fw-bold fs-3 text-primary">{{ number_format($stats['total_orders']) }}</div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary mt-2">
                عرض كل الطلبات <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chart + Recent Orders -->
<div class="row g-3">
    <div class="col-md-7">
        <div class="card table-card p-3">
            <div class="fw-bold mb-3">📊 أرباح آخر 7 أيام</div>
            <div class="chart-container">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card table-card">
            <div class="card-header bg-white border-0 fw-bold pt-3">آخر الطلبات</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>البائع</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-bold">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="text-muted small">{{ $order->reseller?->name }}</td>
                            <td>
                                <span class="badge bg-{{ $order->status_color }} rounded-pill">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('profitChart').getContext('2d');
const chartData = @json($chartData);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [{
            label: 'الأرباح (د.ع)',
            data: chartData.map(d => d.profit),
            backgroundColor: 'rgba(37, 99, 235, 0.15)',
            borderColor: '#2563EB',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => v.toLocaleString('ar-IQ') }
            }
        }
    }
});
</script>
@endpush
