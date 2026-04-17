@extends('admin.layouts.app')
@section('title', 'إدارة البائعين')
@section('page-title', 'البائعون / المسوّقون')

@section('content')

<!-- Filters -->
<div class="card table-card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="🔍 الاسم أو الهاتف" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">كل الحالات</option>
                <option value="active"  @selected(request('status') === 'active')>نشط</option>
                <option value="pending" @selected(request('status') === 'pending')>قيد المراجعة</option>
                <option value="banned"  @selected(request('status') === 'banned')>محظور</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">بحث</button></div>
        <div class="col-md-2"><a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">مسح</a></div>
    </form>
</div>

<div class="text-muted small mb-2">{{ $users->total() }} بائع</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px" class="text-center">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px" class="text-center">#</th>
                    <th>البائع</th>
                    <th>الهاتف</th>
                    <th>المدينة</th>
                    <th class="text-center">الرصيد</th>
                    <th class="text-center">إجمالي الأرباح</th>
                    <th class="text-center">الطلبات</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="width:40px">
                        <input type="checkbox" class="form-check-input row-check" value="{{ $user->id }}">
                    </td>
                    <td class="text-muted small text-center">{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold small">{{ $user->name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.7rem">{{ $user->created_at->locale('ar')->diffForHumans() }}</div>
                    </td>
                    <td class="small">{{ $user->phone }}</td>
                    <td class="small">{{ $user->city ?? '—' }}</td>
                    <td class="text-center fw-bold text-success">{{ number_format($user->wallet?->balance ?? 0) }} د.ع</td>
                    <td class="text-center text-muted small">{{ number_format($user->wallet?->total_earned ?? 0) }} د.ع</td>
                    <td class="text-center">{{ $user->orders_count ?? 0 }}</td>
                    <td class="text-center">
                        @if($user->status === 'active')
                            <span class="badge bg-success">نشط</span>
                        @elseif($user->status === 'pending')
                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                        @else
                            <span class="badge bg-danger">محظور</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                @csrf
                                <button class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        title="{{ $user->status === 'active' ? 'حظر' : 'تفعيل' }}">
                                    <i class="bi bi-{{ $user->status === 'active' ? 'slash-circle' : 'check-circle' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('هل تريد حذف البائع {{ addslashes($user->name) }} نهائياً؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">لا يوجد بائعون</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $users])
</div>

@endsection
