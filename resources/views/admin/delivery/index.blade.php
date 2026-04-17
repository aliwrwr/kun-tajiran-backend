@extends('admin.layouts.app')

@section('title', 'مندوبو التوصيل')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">مندوبو التوصيل</h4>
    <a href="{{ route('admin.delivery.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> إضافة مندوب
    </a>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 text-center p-3">
            <div class="fs-2 fw-black text-success">{{ $availableCount }}</div>
            <div class="text-muted">متاح</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 text-center p-3">
            <div class="fs-2 fw-black text-warning">{{ $busyCount }}</div>
            <div class="text-muted">مشغول</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card shadow-sm border-0 text-center p-3">
            <div class="fs-2 fw-black text-secondary">{{ $offlineCount }}</div>
            <div class="text-muted">غير متاح</div>
        </div>
    </div>
</div>

<div class="card shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px">#</th>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>المحافظة</th>
                    <th>الحالة</th>
                    <th>الطلبات النشطة</th>
                    <th>تاريخ الإنضمام</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-check" value="{{ $agent->id }}">
                        </td>
                        <td class="text-muted small">{{ $agents->firstItem() + $loop->index }}</td>
                        <td class="fw-bold">{{ $agent->user->name }}</td>
                        <td>{{ $agent->user->phone }}</td>
                        <td>{{ $agent->city ?? '—' }}</td>
                        <td>
                            @php
                                $sc = ['available'=>'success','busy'=>'warning','offline'=>'secondary'];
                                $sl = ['available'=>'متاح','busy'=>'مشغول','offline'=>'غير متاح'];
                            @endphp
                            <span class="badge bg-{{ $sc[$agent->status] ?? 'secondary' }}">{{ $sl[$agent->status] ?? $agent->status }}</span>
                        </td>
                        <td>{{ $agent->activeOrders()->count() }}</td>
                        <td>{{ $agent->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('admin.delivery.show', $agent) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-5">لا يوجد مندوبون مسجلون بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $agents])
</div>
@endsection
