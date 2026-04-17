@extends('admin.layouts.app')
@section('title', 'إدارة السحوبات')
@section('page-title', 'طلبات السحب')

@section('content')

<!-- Summary -->
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-cash-coin me-2"></i>
    إجمالي المبالغ المعلقة: <strong>{{ number_format($pendingTotal) }} د.ع</strong>
</div>

<!-- Filters -->
<div class="card table-card p-3 mb-4">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">كل الحالات</option>
                <option value="pending"   @selected(request('status') === 'pending')>قيد المراجعة</option>
                <option value="processed" @selected(request('status') === 'processed')>تمت المعالجة</option>
                <option value="rejected"  @selected(request('status') === 'rejected')>مرفوض</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary">بحث</button></div>
        <div class="col-md-2"><a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary">مسح</a></div>
    </form>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px">#</th>
                    <th>البائع</th>
                    <th>المبلغ</th>
                    <th>طريقة الاستلام</th>
                    <th>رقم الحساب</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="{{ $w->id }}">
                    </td>
                    <td class="text-muted small">{{ $withdrawals->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-bold small">{{ $w->user?->name }}</div>
                        <div class="text-muted" style="font-size:.7rem">{{ $w->user?->phone }}</div>
                    </td>
                    <td class="fw-bold text-danger">{{ number_format($w->amount) }} د.ع</td>
                    <td class="small">{{ $w->method_label }}</td>
                    <td class="small text-muted">{{ $w->account_number ?? '—' }}</td>
                    <td>
                        @if($w->status === 'pending')
                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                        @elseif($w->status === 'processed')
                            <span class="badge bg-success">تمت المعالجة</span>
                        @else
                            <span class="badge bg-danger">مرفوض</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $w->created_at->format('Y/m/d') }}</td>
                    <td>
                        @if($w->status === 'pending')
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#processModal{{ $w->id }}">
                            معالجة
                        </button>

                        <!-- Process Modal -->
                        <div class="modal fade" id="processModal{{ $w->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">معالجة طلب السحب #{{ $w->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            البائع: <strong>{{ $w->user?->name }}</strong><br>
                                            المبلغ: <strong class="text-danger">{{ number_format($w->amount) }} د.ع</strong><br>
                                            الطريقة: <strong>{{ $w->method_label }}</strong><br>
                                            الحساب: <strong>{{ $w->account_number }}</strong>
                                        </p>
                                        <form method="POST" action="{{ route('admin.withdrawals.process', $w) }}" id="form{{ $w->id }}">
                                            @csrf
                                            <input type="hidden" name="action" id="action{{ $w->id }}">
                                            <div class="mb-3">
                                                <label class="form-label">ملاحظات (اختياري)</label>
                                                <textarea name="admin_notes" class="form-control" rows="2"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                                onclick="document.getElementById('action{{ $w->id }}').value='rejected';document.getElementById('form{{ $w->id }}').submit()">
                                            رفض
                                        </button>
                                        <button type="button" class="btn btn-success"
                                                onclick="document.getElementById('action{{ $w->id }}').value='approved';document.getElementById('form{{ $w->id }}').submit()">
                                            تأكيد وخصم من المحفظة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">لا توجد طلبات سحب</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $withdrawals])
</div>

@endsection
