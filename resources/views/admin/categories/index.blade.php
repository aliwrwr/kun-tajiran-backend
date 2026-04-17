@extends('admin.layouts.app')
@section('title', 'إدارة الأقسام')
@section('page-title', 'إدارة الأقسام')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="text-muted small">{{ $categories->total() }} قسم</div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> إضافة قسم
    </a>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px">#</th>
                    <th>القسم</th>
                    <th>الاسم بالعربية</th>
                    <th>الأيقونة</th>
                    <th>عدد المنتجات</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr class="category-row"
                    data-id="{{ $category->id }}"
                    data-show="{{ route('admin.categories.show', $category) }}"
                    data-edit="{{ route('admin.categories.edit', $category) }}"
                    data-toggle="{{ route('admin.categories.toggle-status', $category) }}"
                    data-delete="{{ route('admin.categories.destroy', $category) }}"
                    data-active="{{ $category->is_active ? '1' : '0' }}"
                    data-deletable="{{ $category->products_count == 0 ? '1' : '0' }}"
                    data-token="{{ csrf_token() }}">
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="{{ $category->id }}">
                    </td>
                    <td class="text-muted small">{{ $categories->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt=""
                                     style="width:40px;height:40px;object-fit:cover;border-radius:.5rem;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="width:40px;height:40px;font-size:1.2rem;">
                                    {{ $category->icon ?? '📦' }}
                                </div>
                            @endif
                            <span class="fw-semibold">{{ $category->name }}</span>
                        </div>
                    </td>
                    <td>{{ $category->name_ar }}</td>
                    <td style="font-size:1.4rem">{{ $category->icon ?? '—' }}</td>
                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                            {{ $category->products_count }} منتج
                        </span>
                    </td>
                    <td>
                        @if($category->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success">نشط</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">معطل</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.categories.toggle-status', $category) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="تغيير الحالة">
                                    <i class="bi bi-toggle-{{ $category->is_active ? 'on' : 'off' }}"></i>
                                </button>
                            </form>
                            @if($category->products_count == 0)
                            <form method="POST"
                                  action="{{ route('admin.categories.destroy', $category) }}"
                                  onsubmit="return confirm('هل تريد حذف هذا القسم؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-grid fs-1 d-block mb-2"></i>
                        لا توجد أقسام بعد
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $categories])
</div>

@endsection

{{-- Context Menu --}}
<div id="ctx-menu" class="ctx-menu shadow-lg" style="display:none;position:fixed;z-index:9999;min-width:200px;border-radius:.75rem;overflow:hidden;background:#fff;border:1px solid rgba(0,0,0,.08);">
    <div class="ctx-header px-3 py-2 border-bottom" style="background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;font-size:.8rem;font-weight:600;">
        <i class="bi bi-grid me-1"></i>
        <span id="ctx-cat-name">قسم</span>
    </div>
    <div class="ctx-items py-1">
        <a href="{{ route('admin.categories.create') }}" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-primary-subtle text-primary"><i class="bi bi-plus-circle"></i></span>
            إضافة قسم جديد
        </a>
        <div class="ctx-divider"></div>
        <a href="#" id="ctx-show" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-info-subtle text-info"><i class="bi bi-eye"></i></span>
            عرض التفاصيل
        </a>
        <a href="#" id="ctx-edit" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-warning-subtle text-warning"><i class="bi bi-pencil-square"></i></span>
            تعديل القسم
        </a>
        <button id="ctx-toggle" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none w-100 border-0 bg-transparent text-dark text-start">
            <span class="ctx-icon" id="ctx-toggle-icon"><i class="bi bi-toggle-on"></i></span>
            <span id="ctx-toggle-label">إيقاف القسم</span>
        </button>
        <div class="ctx-divider"></div>
        <button id="ctx-delete" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-danger w-100 border-0 bg-transparent text-start">
            <span class="ctx-icon bg-danger-subtle text-danger"><i class="bi bi-trash3"></i></span>
            <span id="ctx-delete-label">حذف القسم</span>
        </button>
    </div>
</div>

{{-- Hidden forms --}}
<form id="ctx-toggle-form" method="POST" style="display:none;">@csrf</form>
<form id="ctx-delete-form" method="POST" style="display:none;">@csrf @method('DELETE')</form>

@push('styles')
<style>
.ctx-menu { animation: ctxFadeIn .15s ease; }
@keyframes ctxFadeIn { from { opacity:0; transform:scale(.95) translateY(-4px); } to { opacity:1; transform:scale(1) translateY(0); } }
.ctx-icon { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:.4rem;font-size:.85rem;flex-shrink:0; }
.ctx-item { font-size:.85rem;transition:background .15s;cursor:pointer; }
.ctx-item:hover { background:rgba(99,102,241,.07); }
.ctx-item:hover .ctx-icon { transform:scale(1.1); }
.ctx-divider { height:1px;background:rgba(0,0,0,.06);margin:.25rem .75rem; }
.category-row { cursor:context-menu; }
.category-row.ctx-active { background:rgba(14,165,233,.07)!important; }
.ctx-disabled { opacity:.45;pointer-events:none; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const menu        = document.getElementById('ctx-menu');
    const toggleForm  = document.getElementById('ctx-toggle-form');
    const deleteForm  = document.getElementById('ctx-delete-form');
    const toggleIcon  = document.getElementById('ctx-toggle-icon');
    const toggleLabel = document.getElementById('ctx-toggle-label');
    const deleteBtn   = document.getElementById('ctx-delete');
    const deleteLbl   = document.getElementById('ctx-delete-label');
    let activeRow     = null;

    document.querySelectorAll('.category-row').forEach(row => {
        row.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            if (activeRow) activeRow.classList.remove('ctx-active');
            activeRow = this;
            this.classList.add('ctx-active');

            // Set name
            const name = this.querySelector('.fw-semibold')?.textContent?.trim() ?? 'قسم';
            document.getElementById('ctx-cat-name').textContent = name.length > 22 ? name.slice(0,22)+'…' : name;

            // Show / Edit
            document.getElementById('ctx-show').href = this.dataset.show;
            document.getElementById('ctx-edit').href = this.dataset.edit;

            // Toggle button
            const isActive = this.dataset.active === '1';
            toggleIcon.className = 'ctx-icon ' + (isActive ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');
            toggleIcon.innerHTML  = `<i class="bi bi-toggle-${isActive ? 'on' : 'off'}"></i>`;
            toggleLabel.textContent = isActive ? 'إيقاف القسم' : 'تفعيل القسم';
            toggleForm.action = this.dataset.toggle;

            // Delete button
            const isDeletable = this.dataset.deletable === '1';
            deleteBtn.classList.toggle('ctx-disabled', !isDeletable);
            deleteLbl.textContent = isDeletable ? 'حذف القسم' : 'لا يمكن الحذف (يحتوي منتجات)';
            deleteForm.action = this.dataset.delete;

            // Position
            const vw = window.innerWidth, vh = window.innerHeight;
            const mw = 210, mh = 250;
            let x = e.clientX, y = e.clientY;
            if (x + mw > vw) x = vw - mw - 8;
            if (y + mh > vh) y = vh - mh - 8;
            menu.style.left    = x + 'px';
            menu.style.top     = y + 'px';
            menu.style.display = 'block';
        });

        // Long-press touch
        let pressTimer;
        row.addEventListener('touchstart', function (e) {
            pressTimer = setTimeout(() => {
                e.preventDefault();
                e.target.closest('.category-row')?.dispatchEvent(
                    new MouseEvent('contextmenu', { bubbles:true, clientX: e.touches[0].clientX, clientY: e.touches[0].clientY })
                );
            }, 600);
        });
        row.addEventListener('touchend',  () => clearTimeout(pressTimer));
        row.addEventListener('touchmove', () => clearTimeout(pressTimer));
    });

    document.getElementById('ctx-toggle').addEventListener('click', function () {
        toggleForm.submit();
        hideMenu();
    });

    deleteBtn.addEventListener('click', function () {
        if (this.classList.contains('ctx-disabled')) return;
        const name = document.getElementById('ctx-cat-name').textContent;
        if (!confirm(`هل تريد حذف قسم "${name}" ؟`)) return;
        deleteForm.submit();
        hideMenu();
    });

    function hideMenu() {
        menu.style.display = 'none';
        if (activeRow) { activeRow.classList.remove('ctx-active'); activeRow = null; }
    }
    document.addEventListener('click',   hideMenu);
    document.addEventListener('keydown',  e => e.key === 'Escape' && hideMenu());
    document.addEventListener('scroll',   hideMenu, true);
    menu.addEventListener('click', e => e.stopPropagation());
})();
</script>
@endpush
