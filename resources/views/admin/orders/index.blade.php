@extends('admin.layouts.app')
@section('title', 'إدارة الطلبات')
@section('page-title', 'إدارة الطلبات')

@section('content')

<!-- Status Filter Tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    @php
        $statuses = [
            '' => ['label' => 'الكل', 'color' => 'secondary'],
            'new' => ['label' => 'جديد', 'color' => 'primary'],
            'confirmed' => ['label' => 'مؤكد', 'color' => 'info'],
            'preparing' => ['label' => 'قيد التجهيز', 'color' => 'warning'],
            'out_for_delivery' => ['label' => 'قيد التوصيل', 'color' => 'purple'],
            'delivered' => ['label' => 'تم التسليم', 'color' => 'success'],
            'rejected' => ['label' => 'مرفوض', 'color' => 'danger'],
        ];
    @endphp
    @foreach($statuses as $key => $s)
        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['status' => $key])) }}"
           class="btn btn-sm {{ request('status') === $key ? 'btn-'.$s['color'] : 'btn-outline-'.$s['color'] }}">
            {{ $s['label'] }}
        </a>
    @endforeach
</div>

<!-- Search -->
<div class="card table-card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control"
                   placeholder="🔍 رقم الطلب / اسم الزبون / الهاتف"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="city" class="form-control" placeholder="المدينة" value="{{ request('city') }}">
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">بحث</button></div>
        <div class="col-md-2"><a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100">مسح</a></div>
    </form>
</div>

<div class="text-muted small mb-2">{{ $orders->total() }} طلب</div>

<!-- Orders Table -->
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px">#</th>
                    <th>رقم الطلب</th>
                    <th>الزبون</th>
                    <th>المدينة</th>
                    <th>البائع</th>
                    <th>المبلغ الكلي</th>
                    <th>ربح البائع</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="order-row"
                    data-id="{{ $order->id }}"
                    data-number="{{ $order->order_number }}"
                    data-show="{{ route('admin.orders.show', $order) }}"
                    data-reseller="{{ $order->reseller_id ? route('admin.users.show', $order->reseller_id) : '' }}"
                    data-reseller-name="{{ $order->reseller?->name ?? '' }}"
                    data-status="{{ route('admin.orders.status', $order) }}"
                    data-assign="{{ route('admin.orders.assign', $order) }}"
                    data-delete="{{ route('admin.orders.destroy', $order) }}"
                    data-print="{{ route('admin.orders.print', $order) }}"
                    data-current-status="{{ $order->status }}"
                    data-token="{{ csrf_token() }}">
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="{{ $order->id }}">
                    </td>
                    <td class="text-muted small">{{ $orders->firstItem() + $loop->index }}</td>
                    <td class="fw-bold">{{ $order->order_number }}</td>
                    <td>
                        <div class="small fw-bold">{{ $order->customer_name }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $order->customer_phone }}</div>
                    </td>
                    <td class="small">{{ $order->customer_city }}</td>
                    <td class="small text-muted">{{ $order->reseller?->name }}</td>
                    <td class="fw-bold">{{ number_format($order->total_sale_price) }}</td>
                    <td class="text-success">{{ number_format($order->reseller_profit) }}</td>
                    <td>
                        <span class="badge bg-{{ $order->status_color }} rounded-pill px-2">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-5">
                        <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
                        لا توجد طلبات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $orders])
</div>

@endsection

{{-- ===== Context Menu ===== --}}
<div id="ctx-menu" style="display:none;position:fixed;z-index:9999;min-width:210px;border-radius:.75rem;overflow:visible;background:#fff;border:1px solid rgba(0,0,0,.08);box-shadow:0 8px 32px rgba(0,0,0,.13);">
    {{-- Header --}}
    <div class="ctx-header px-3 py-2 border-bottom" style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;font-size:.8rem;font-weight:600;border-radius:.75rem .75rem 0 0;">
        <i class="bi bi-bag me-1"></i>
        <span id="ctx-order-number">طلب</span>
    </div>
    <div class="py-1">

        {{-- عرض التفاصيل --}}
        <a href="#" id="ctx-show" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-info-subtle text-info"><i class="bi bi-eye"></i></span>
            عرض التفاصيل
        </a>

        {{-- طباعة التفاصيل --}}
        <a href="#" id="ctx-print" target="_blank" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-secondary-subtle text-secondary"><i class="bi bi-printer"></i></span>
            طباعة الفاتورة
        </a>

        {{-- عرض ملف البائع --}}
        <a href="#" id="ctx-reseller" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-primary-subtle text-primary"><i class="bi bi-person-badge"></i></span>
            عرض ملف البائع
        </a>

        <div class="ctx-divider"></div>

        {{-- الحالة (sub-menu) --}}
        <div class="ctx-has-sub position-relative">
            <div class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-dark" id="ctx-status-trigger">
                <span class="ctx-icon bg-warning-subtle text-warning"><i class="bi bi-arrow-repeat"></i></span>
                <span class="flex-grow-1">تغيير الحالة</span>
                <i class="bi bi-chevron-left small text-muted"></i>
            </div>
            {{-- sub-menu --}}
            <div id="ctx-status-sub" class="ctx-submenu shadow" style="display:none;position:absolute;top:0;right:100%;min-width:170px;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:.6rem;padding:.35rem 0;">
                @foreach([
                    ['key'=>'confirmed',       'label'=>'مؤكد',           'color'=>'text-primary',  'icon'=>'bi-check-circle'],
                    ['key'=>'preparing',       'label'=>'قيد التجهيز',    'color'=>'text-warning',  'icon'=>'bi-box-seam'],
                    ['key'=>'out_for_delivery','label'=>'قيد التوصيل',    'color'=>'text-info',     'icon'=>'bi-truck'],
                    ['key'=>'delivered',       'label'=>'تم التسليم',     'color'=>'text-success',  'icon'=>'bi-check2-all'],
                    ['key'=>'rejected',        'label'=>'مرفوض',          'color'=>'text-danger',   'icon'=>'bi-x-circle'],
                ] as $s)
                <button class="ctx-item ctx-status-btn d-flex align-items-center gap-2 px-3 py-2 w-100 border-0 bg-transparent text-start"
                        data-status="{{ $s['key'] }}">
                    <i class="bi {{ $s['icon'] }} {{ $s['color'] }} fw-bold"></i>
                    <span class="small">{{ $s['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- اختيار مندوب (sub-menu) --}}
        <div class="ctx-has-sub position-relative">
            <div class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-dark" id="ctx-agent-trigger">
                <span class="ctx-icon bg-success-subtle text-success"><i class="bi bi-scooter"></i></span>
                <span class="flex-grow-1">اختيار مندوب</span>
                <i class="bi bi-chevron-left small text-muted"></i>
            </div>
            <div id="ctx-agent-sub" class="ctx-submenu shadow" style="display:none;position:absolute;top:0;right:100%;min-width:180px;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:.6rem;padding:.35rem 0;max-height:220px;overflow-y:auto;">
                @forelse($agents as $agent)
                <button class="ctx-item ctx-agent-btn d-flex align-items-center gap-2 px-3 py-2 w-100 border-0 bg-transparent text-start"
                        data-agent-id="{{ $agent->id }}">
                    <span class="ctx-icon bg-success-subtle text-success" style="font-size:.7rem;width:24px;height:24px;">
                        <i class="bi bi-person"></i>
                    </span>
                    <span class="small">{{ $agent->user?->name ?? 'مندوب #'.$agent->id }}</span>
                </button>
                @empty
                <div class="px-3 py-2 text-muted small">لا يوجد مندوبون متاحون</div>
                @endforelse
            </div>
        </div>

        <div class="ctx-divider"></div>

        {{-- حذف --}}
        <button id="ctx-delete" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-danger w-100 border-0 bg-transparent text-start">
            <span class="ctx-icon bg-danger-subtle text-danger"><i class="bi bi-trash3"></i></span>
            حذف الطلب
        </button>
    </div>
</div>

{{-- Hidden forms --}}
<form id="ctx-status-form" method="POST" style="display:none;">
    @csrf <input type="hidden" name="status" id="ctx-status-value">
</form>
<form id="ctx-assign-form" method="POST" style="display:none;">
    @csrf <input type="hidden" name="delivery_agent_id" id="ctx-agent-value">
</form>
<form id="ctx-delete-form" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@push('styles')
<style>
.ctx-menu,.ctx-submenu { animation: ctxIn .13s ease; }
@keyframes ctxIn { from{opacity:0;transform:scale(.95) translateY(-4px)} to{opacity:1;transform:scale(1) translateY(0)} }
.ctx-icon { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:.4rem;font-size:.85rem;flex-shrink:0;transition:transform .15s; }
.ctx-item { font-size:.85rem;transition:background .12s;cursor:pointer;user-select:none; }
.ctx-item:hover { background:rgba(99,102,241,.07); }
.ctx-item:hover .ctx-icon { transform:scale(1.12); }
.ctx-divider { height:1px;background:rgba(0,0,0,.06);margin:.3rem .75rem; }
.order-row { cursor:context-menu; }
.order-row.ctx-active { background:rgba(245,158,11,.07)!important; }
.ctx-has-sub:hover > div:last-child { display:block!important; }
#ctx-status-sub .ctx-item:hover, #ctx-agent-sub .ctx-item:hover { background:rgba(99,102,241,.07); }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const menu       = document.getElementById('ctx-menu');
    const statusForm = document.getElementById('ctx-status-form');
    const assignForm = document.getElementById('ctx-assign-form');
    const deleteForm = document.getElementById('ctx-delete-form');
    let   activeRow  = null;

    /* ---- Open menu ---- */
    document.querySelectorAll('.order-row').forEach(row => {
        row.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            if (activeRow) activeRow.classList.remove('ctx-active');
            activeRow = this;
            this.classList.add('ctx-active');

            // Show / Edit / Print
            document.getElementById('ctx-order-number').textContent = '#' + (this.dataset.number ?? '');
            document.getElementById('ctx-show').href  = this.dataset.show;
            document.getElementById('ctx-print').href = this.dataset.print;

            // Reseller link
            const resellerEl = document.getElementById('ctx-reseller');
            if (this.dataset.reseller) {
                resellerEl.href = this.dataset.reseller;
                resellerEl.classList.remove('ctx-disabled');
                resellerEl.title = this.dataset.resellerName;
            } else {
                resellerEl.href = '#';
                resellerEl.classList.add('ctx-disabled');
                resellerEl.title = 'لا يوجد بائع';
            }

            // Forms actions
            statusForm.action = this.dataset.status;
            assignForm.action = this.dataset.assign;
            deleteForm.action = this.dataset.delete;

            // Highlight current status in sub-menu
            const cur = this.dataset.currentStatus;
            document.querySelectorAll('.ctx-status-btn').forEach(btn => {
                btn.style.fontWeight = btn.dataset.status === cur ? '700' : '';
                btn.style.background = btn.dataset.status === cur ? 'rgba(99,102,241,.08)' : '';
            });

            // Position
            const vw = window.innerWidth, vh = window.innerHeight;
            const mw = 220, mh = 320;
            let x = e.clientX, y = e.clientY;
            if (x + mw > vw) x = vw - mw - 8;
            if (y + mh > vh) y = vh - mh - 8;
            menu.style.left    = x + 'px';
            menu.style.top     = y + 'px';
            menu.style.display = 'block';
        });

        // Long-press touch
        let t;
        row.addEventListener('touchstart', e => {
            t = setTimeout(() => {
                e.target.closest('.order-row')?.dispatchEvent(
                    new MouseEvent('contextmenu', { bubbles:true, clientX:e.touches[0].clientX, clientY:e.touches[0].clientY })
                );
            }, 600);
        });
        row.addEventListener('touchend',  () => clearTimeout(t));
        row.addEventListener('touchmove', () => clearTimeout(t));
    });

    /* ---- Status buttons ---- */
    document.querySelectorAll('.ctx-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('ctx-status-value').value = this.dataset.status;
            statusForm.submit();
            hideMenu();
        });
    });

    /* ---- Agent buttons ---- */
    document.querySelectorAll('.ctx-agent-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('ctx-agent-value').value = this.dataset.agentId;
            assignForm.submit();
            hideMenu();
        });
    });

    /* ---- Delete ---- */
    document.getElementById('ctx-delete').addEventListener('click', function () {
        const num = document.getElementById('ctx-order-number').textContent;
        if (!confirm('هل تريد حذف الطلب ' + num + ' ؟')) return;
        deleteForm.submit();
        hideMenu();
    });

    /* ---- Hide ---- */
    function hideMenu() {
        menu.style.display = 'none';
        if (activeRow) { activeRow.classList.remove('ctx-active'); activeRow = null; }
    }
    document.addEventListener('click',   hideMenu);
    document.addEventListener('keydown', e => e.key === 'Escape' && hideMenu());
    document.addEventListener('scroll',  hideMenu, true);
    menu.addEventListener('click', e => e.stopPropagation());
})();
</script>
@endpush
