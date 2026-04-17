@extends('admin.layouts.app')
@section('title', 'تقرير الأرباح')
@section('page-title', 'تقرير أرباح البائعين')

@section('content')

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card table-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#ecfdf5">
                    <i class="bi bi-cash-coin fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">إجمالي الأرباح المدفوعة</div>
                    <div class="fw-bold fs-6">{{ number_format($totals->total_earned ?? 0) }} <small class="text-muted">د.ع</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card table-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#eff6ff">
                    <i class="bi bi-wallet2 fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">رصيد قابل للسحب</div>
                    <div class="fw-bold fs-6">{{ number_format($totals->total_balance ?? 0) }} <small class="text-muted">د.ع</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card table-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#fef3c7">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">أرباح معلقة</div>
                    <div class="fw-bold fs-6">{{ number_format($totals->total_pending ?? 0) }} <small class="text-muted">د.ع</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card table-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#fee2e2">
                    <i class="bi bi-arrow-up-circle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="text-muted small">إجمالي السحوبات</div>
                    <div class="fw-bold fs-6">{{ number_format($totals->total_withdrawn ?? 0) }} <small class="text-muted">د.ع</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Resellers Table -->
    <div class="col-lg-8">
        <!-- Filters -->
        <div class="card table-card p-3 mb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control"
                           placeholder="🔍 بحث باسم أو هاتف..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="city" class="form-select">
                        <option value="">كل المدن</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">بحث</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('admin.profits.index') }}" class="btn btn-outline-secondary w-100">✕</a>
                </div>
            </form>
        </div>

        <div class="card table-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>البائع</th>
                            <th>المدينة</th>
                            <th>الطلبات</th>
                            <th>إجمالي الأرباح</th>
                            <th>الرصيد الحالي</th>
                            <th>السحوبات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resellers as $reseller)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $reseller->name }}</div>
                                <div class="text-muted small">{{ $reseller->phone }}</div>
                            </td>
                            <td>{{ $reseller->city ?? '—' }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ $reseller->orders_count }}
                                </span>
                            </td>
                            <td class="fw-bold text-success">
                                {{ number_format($reseller->wallet->total_earned ?? 0) }} د.ع
                            </td>
                            <td class="fw-bold text-primary">
                                {{ number_format($reseller->wallet->balance ?? 0) }} د.ع
                                @if(($reseller->wallet->pending_balance ?? 0) > 0)
                                    <div class="text-muted small">
                                        معلق: {{ number_format($reseller->wallet->pending_balance) }} د.ع
                                    </div>
                                @endif
                            </td>
                            <td>{{ number_format($reseller->wallet->total_withdrawn ?? 0) }} د.ع</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                لا يوجد بائعون
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($resellers->hasPages())
            <div class="card-footer">{{ $resellers->links() }}</div>
            @endif
        </div>
    </div>

    <!-- Pending Withdrawals -->
    <div class="col-lg-4">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2 text-warning"></i>
                    طلبات سحب معلقة
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($pendingWithdrawals as $req)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $req->user->phone }}</div>
                        </div>
                        <span class="fw-bold text-danger">{{ number_format($req->amount) }} د.ع</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.75rem">
                        {{ $req->method_label ?? $req->method }} — {{ $req->account_number }}
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-all fs-3 d-block mb-2 text-success"></i>
                    لا توجد طلبات معلقة
                </div>
                @endforelse
                @if($pendingWithdrawals->count() > 0)
                <div class="p-3">
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-primary w-100 btn-sm">
                        عرض جميع طلبات السحب
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
