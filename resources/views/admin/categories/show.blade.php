@extends('admin.layouts.app')
@section('title', 'تفاصيل القسم: ' . $category->name_ar)
@section('page-title', 'تفاصيل القسم')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة للأقسام
    </a>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}">
            @csrf
            <button class="btn {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                <i class="bi bi-toggle-{{ $category->is_active ? 'on' : 'off' }} me-1"></i>
                {{ $category->is_active ? 'إيقاف القسم' : 'تفعيل القسم' }}
            </button>
        </form>
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> تعديل
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Left: image + status --}}
    <div class="col-lg-4">
        <div class="card table-card p-4 text-center mb-4">
            @if($category->image)
                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name_ar }}"
                     class="img-fluid rounded-3 mb-3 mx-auto" style="max-width:200px;max-height:200px;object-fit:cover;">
            @else
                <div class="mx-auto mb-3 rounded-3 bg-light d-flex align-items-center justify-content-center"
                     style="width:120px;height:120px;font-size:3.5rem">
                    {{ $category->icon ?? '📦' }}
                </div>
            @endif

            <h5 class="fw-bold mb-1">{{ $category->name_ar }}</h5>
            <p class="text-muted small mb-3">{{ $category->name }}</p>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @if($category->is_active)
                    <span class="badge bg-success bg-opacity-10 text-success py-2 px-3 rounded-pill">
                        <i class="bi bi-check-circle me-1"></i>نشط
                    </span>
                @else
                    <span class="badge bg-danger bg-opacity-10 text-danger py-2 px-3 rounded-pill">
                        <i class="bi bi-x-circle me-1"></i>معطل
                    </span>
                @endif
                @if($category->icon)
                    <span class="badge bg-light text-dark py-2 px-3 rounded-pill">
                        {{ $category->icon }} أيقونة
                    </span>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="card table-card p-4">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase">إحصائيات</h6>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="small text-muted">عدد المنتجات</span>
                <span class="fw-bold text-primary">{{ $category->products_count }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="small text-muted">الترتيب</span>
                <span class="fw-bold">{{ $category->sort_order ?? '—' }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2">
                <span class="small text-muted">تاريخ الإضافة</span>
                <span class="small">{{ $category->created_at->format('Y/m/d') }}</span>
            </div>
        </div>
    </div>

    {{-- Right: last products --}}
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">آخر المنتجات في هذا القسم</span>
                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th class="text-center">سعر البيع</th>
                            <th class="text-center">المخزون</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $i => $product)
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($product->thumbnail)
                                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt=""
                                             style="width:36px;height:36px;object-fit:cover;border-radius:.4rem">
                                    @else
                                        <div class="bg-light rounded" style="width:36px;height:36px"></div>
                                    @endif
                                    <div>
                                        <div class="small fw-semibold">{{ $product->name_ar }}</div>
                                        <div class="text-muted" style="font-size:.7rem">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold small">{{ number_format($product->suggested_price) }} د.ع</td>
                            <td class="text-center">
                                <span class="{{ $product->stock_quantity < 5 ? 'text-danger' : 'text-success' }} fw-bold small">
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($product->is_active && $product->stock_quantity > 0)
                                    <span class="badge bg-success">نشط</span>
                                @elseif(!$product->is_active)
                                    <span class="badge bg-secondary">معطل</span>
                                @else
                                    <span class="badge bg-warning text-dark">نفد</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">لا توجد منتجات في هذا القسم</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
