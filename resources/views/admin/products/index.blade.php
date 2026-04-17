@extends('admin.layouts.app')
@section('title', 'إدارة المنتجات')
@section('page-title', 'إدارة المنتجات')

@section('content')

<!-- Filters -->
<div class="card table-card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="🔍 بحث عن منتج..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">كل الأقسام</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>نشط</option>
                <option value="inactive" @selected(request('status') === 'inactive')>غير نشط</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">بحث</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100">مسح</a>
        </div>
    </form>
</div>

<!-- Add Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">{{ $products->total() }} منتج</div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> إضافة منتج
    </a>
</div>

<!-- Products Table -->
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th style="width:50px">#</th>
                    <th>المنتج</th>
                    <th>القسم</th>
                    <th>سعر الجملة</th>
                    <th>سعر البيع</th>
                    <th>ربح البائع</th>
                    <th>المخزون</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="product-row"
                    data-id="{{ $product->id }}"
                    data-show="{{ route('admin.products.show', $product) }}"
                    data-edit="{{ route('admin.products.edit', $product) }}"
                    data-delete="{{ route('admin.products.destroy', $product) }}"
                    data-token="{{ csrf_token() }}">
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="{{ $product->id }}">
                    </td>
                    <td class="text-muted small">{{ $products->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->thumbnail)
                                <img src="{{ asset('storage/' . $product->thumbnail) }}" alt=""
                                     style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;">
                            @else
                                <div class="bg-light rounded" style="width:44px;height:44px;"></div>
                            @endif
                            <div>
                                <div class="fw-bold small">{{ $product->name_ar }}</div>
                                <div class="text-muted" style="font-size:.7rem">{{ $product->sku }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $product->category?->name_ar }}</span></td>
                    <td class="text-muted">{{ number_format($product->wholesale_price) }} د.ع</td>
                    <td class="fw-bold">{{ number_format($product->suggested_price) }} د.ع</td>
                    <td class="text-success fw-bold">{{ number_format($product->reseller_profit) }} د.ع</td>
                    <td>
                        <span class="{{ $product->stock_quantity < 5 ? 'text-danger' : 'text-success' }} fw-bold">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td>
                        @if($product->is_active && $product->stock_quantity > 0)
                            <span class="badge bg-success">نشط</span>
                        @elseif(!$product->is_active)
                            <span class="badge bg-secondary">معطل</span>
                        @else
                            <span class="badge bg-warning text-dark">نفد المخزون</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('حذف المنتج؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                        لا توجد منتجات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination-bar', ['paginator' => $products])
</div>

@endsection

{{-- Context Menu --}}
<div id="ctx-menu" class="ctx-menu shadow-lg" style="display:none;position:fixed;z-index:9999;min-width:190px;border-radius:.75rem;overflow:hidden;background:#fff;border:1px solid rgba(0,0,0,.08);backdrop-filter:blur(6px);">
    <div class="ctx-header px-3 py-2 border-bottom" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:.8rem;font-weight:600;">
        <i class="bi bi-box-seam me-1"></i>
        <span id="ctx-product-name">منتج</span>
    </div>
    <div class="ctx-items py-1">
        <a href="{{ route('admin.products.create') }}" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-primary-subtle text-primary"><i class="bi bi-plus-circle"></i></span>
            إضافة منتج جديد
        </a>
        <div class="ctx-divider"></div>
        <a href="#" id="ctx-show" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-info-subtle text-info"><i class="bi bi-eye"></i></span>
            عرض التفاصيل
        </a>
        <a href="#" id="ctx-edit" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark">
            <span class="ctx-icon bg-warning-subtle text-warning"><i class="bi bi-pencil-square"></i></span>
            تعديل المنتج
        </a>
        <div class="ctx-divider"></div>
        <button id="ctx-delete" class="ctx-item d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-danger w-100 border-0 bg-transparent text-start">
            <span class="ctx-icon bg-danger-subtle text-danger"><i class="bi bi-trash3"></i></span>
            حذف المنتج
        </button>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="ctx-delete-form" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@push('styles')
<style>
.ctx-menu { animation: ctxFadeIn .15s ease; }
@keyframes ctxFadeIn { from { opacity:0; transform:scale(.95) translateY(-4px); } to { opacity:1; transform:scale(1) translateY(0); } }
.ctx-icon { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:.4rem;font-size:.85rem;flex-shrink:0; }
.ctx-item { font-size:.85rem;transition:background .15s,color .15s;cursor:pointer; }
.ctx-item:hover { background:rgba(99,102,241,.07); }
.ctx-item:hover .ctx-icon { transform:scale(1.1); }
.ctx-divider { height:1px;background:rgba(0,0,0,.06);margin:.25rem .75rem; }
.ctx-header { font-family:inherit; }
.product-row { cursor:context-menu; }
.product-row.ctx-active { background:rgba(99,102,241,.07)!important; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const menu    = document.getElementById('ctx-menu');
    const delForm = document.getElementById('ctx-delete-form');
    let   activeRow = null;

    // Show menu
    document.querySelectorAll('.product-row').forEach(row => {
        row.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            if (activeRow) activeRow.classList.remove('ctx-active');
            activeRow = this;
            this.classList.add('ctx-active');

            // Set URLs & name
            const name = this.querySelector('.fw-bold.small')?.textContent?.trim() ?? 'منتج';
            document.getElementById('ctx-product-name').textContent = name.length > 22 ? name.slice(0,22)+'…' : name;
            document.getElementById('ctx-show').href   = this.dataset.show;
            document.getElementById('ctx-edit').href   = this.dataset.edit;
            document.getElementById('ctx-delete').dataset.url   = this.dataset.delete;
            document.getElementById('ctx-delete').dataset.token = this.dataset.token;

            // Position
            const vw = window.innerWidth, vh = window.innerHeight;
            const mw = 200, mh = 220;
            let x = e.clientX, y = e.clientY;
            if (x + mw > vw) x = vw - mw - 8;
            if (y + mh > vh) y = vh - mh - 8;

            menu.style.left    = x + 'px';
            menu.style.top     = y + 'px';
            menu.style.display = 'block';
        });

        // Long-press for touch devices
        let pressTimer;
        row.addEventListener('touchstart', function (e) {
            pressTimer = setTimeout(() => {
                e.preventDefault();
                e.target.closest('.product-row')?.dispatchEvent(
                    new MouseEvent('contextmenu', { bubbles:true, clientX: e.touches[0].clientX, clientY: e.touches[0].clientY })
                );
            }, 600);
        });
        row.addEventListener('touchend',   () => clearTimeout(pressTimer));
        row.addEventListener('touchmove',  () => clearTimeout(pressTimer));
    });

    // Delete action
    document.getElementById('ctx-delete').addEventListener('click', function () {
        const url = this.dataset.url;
        const name = document.getElementById('ctx-product-name').textContent;
        if (!confirm(`هل تريد حذف "${name}" ؟`)) return;
        delForm.action = url;
        delForm.submit();
        hideMenu();
    });

    // Hide on click/scroll/escape
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
