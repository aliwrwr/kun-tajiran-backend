@extends('admin.layouts.app')
@section('title', 'تفاصيل المنتج: ' . $product->name_ar)
@section('page-title', 'تفاصيل المنتج')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> تعديل
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> القائمة
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Images & Basic Info -->
    <div class="col-lg-5">
        <!-- Main Image -->
        @if($product->thumbnail)
        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name_ar }}"
             class="img-fluid rounded-3 mb-3 w-100" style="max-height:320px;object-fit:cover;">
        @else
        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mb-3"
             style="height:240px">
            <i class="bi bi-image text-muted" style="font-size:4rem"></i>
        </div>
        @endif

        <!-- Gallery -->
        @php $images = is_array($product->images) ? $product->images : json_decode($product->images ?? '[]', true); @endphp
        @if(count($images) > 1)
        <div class="d-flex gap-2 flex-wrap mb-3">
            @foreach($images as $img)
            <img src="{{ asset('storage/' . $img) }}" alt=""
                 style="width:64px;height:64px;object-fit:cover;border-radius:.5rem;border:2px solid #e2e8f0">
            @endforeach
        </div>
        @endif

        <!-- Status Badges -->
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $product->is_active ? 'text-success' : 'text-danger' }} py-2 px-3">
                {{ $product->is_active ? 'نشط' : 'غير نشط' }}
            </span>
            @if($product->is_featured)
            <span class="badge bg-warning bg-opacity-10 text-warning py-2 px-3">⭐ مميز</span>
            @endif
            @if($product->stock_quantity <= 5)
            <span class="badge bg-danger bg-opacity-10 text-danger py-2 px-3">
                ⚠️ مخزون منخفض: {{ $product->stock_quantity }}
            </span>
            @endif
        </div>
    </div>

    <!-- Details -->
    <div class="col-lg-7">
        <div class="card table-card mb-3">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">{{ $product->name_ar }}</h5>
                <p class="text-muted small mb-3">{{ $product->name }} — SKU: {{ $product->sku }}</p>

                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="p-3 rounded-3 text-center" style="background:#f8fafc">
                            <div class="text-muted small mb-1">سعر الجملة</div>
                            <div class="fw-bold text-danger">{{ number_format($product->wholesale_price) }} <small>د.ع</small></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 rounded-3 text-center" style="background:#eff6ff">
                            <div class="text-muted small mb-1">سعر البيع المقترح</div>
                            <div class="fw-bold text-primary">{{ number_format($product->suggested_price) }} <small>د.ع</small></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 rounded-3 text-center" style="background:#ecfdf5">
                            <div class="text-muted small mb-1">ربح البائع</div>
                            <div class="fw-bold text-success">{{ number_format($product->reseller_profit) }} <small>د.ع</small></div>
                        </div>
                    </div>
                </div>

                <table class="table table-sm">
                    <tr>
                        <td class="text-muted">الحد الأدنى للبيع</td>
                        <td class="fw-semibold">{{ number_format($product->min_price) }} د.ع</td>
                    </tr>
                    <tr>
                        <td class="text-muted">القسم</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $product->category?->name_ar ?? '—' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">المخزون</td>
                        <td class="fw-bold {{ $product->stock_quantity <= 5 ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock_quantity }} قطعة
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">المبيعات</td>
                        <td>{{ $product->sales_count }} طلب</td>
                    </tr>
                    @if($product->weight)
                    <tr>
                        <td class="text-muted">الوزن</td>
                        <td>{{ $product->weight }} كغ</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Description -->
        @if($product->description_ar || $product->description)
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">الوصف</h6>
            </div>
            <div class="card-body p-4">
                @if($product->description_ar)
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-1">بالعربية</div>
                    <p class="mb-0">{{ $product->description_ar }}</p>
                </div>
                @endif
                @if($product->description)
                <div>
                    <div class="text-muted small fw-semibold mb-1">English</div>
                    <p class="mb-0 text-muted" dir="ltr">{{ $product->description }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Adjust Stock -->
<div class="card table-card mt-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-boxes me-2 text-primary"></i>تعديل المخزون</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.products.stock', $product) }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label fw-semibold">نوع العملية</label>
                <select name="action" class="form-select">
                    <option value="add">إضافة للمخزون</option>
                    <option value="subtract">خصم من المخزون</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">الكمية</label>
                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">الملاحظة</label>
                <input type="text" name="reason" class="form-control" placeholder="سبب التعديل (اختياري)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">تطبيق</button>
            </div>
        </form>
    </div>
</div>

@endsection
