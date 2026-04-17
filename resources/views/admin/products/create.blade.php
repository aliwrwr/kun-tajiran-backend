@extends('admin.layouts.app')
@section('title', 'إضافة منتج جديد')
@section('page-title', 'إضافة منتج جديد')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
@csrf

<div class="card table-card p-4 mb-3">
    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle me-1"></i> معلومات المنتج</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">اسم المنتج (عربي) *</label>
            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                   value="{{ old('name_ar') }}" placeholder="مثال: هاتف سامسونج A54" required>
            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">اسم المنتج (إنجليزي)</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Samsung A54">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">القسم *</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">اختر القسم</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name_ar }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">الوزن</label>
            <input type="text" name="weight" class="form-control" value="{{ old('weight') }}" placeholder="500g">
        </div>
        <div class="col-12">
            <label class="form-label fw-bold">الوصف (عربي)</label>
            <textarea name="description_ar" class="form-control" rows="3"
                      placeholder="اكتب وصف المنتج للبائعين...">{{ old('description_ar') }}</textarea>
        </div>
    </div>
</div>

<div class="card table-card p-4 mb-3">
    <h6 class="fw-bold mb-3 text-success"><i class="bi bi-currency-dollar me-1"></i> الأسعار والمخزون</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-bold">سعر الجملة (د.ع) *</label>
            <input type="number" name="wholesale_price" id="wholesale_price"
                   class="form-control @error('wholesale_price') is-invalid @enderror"
                   value="{{ old('wholesale_price', 0) }}" min="0" oninput="calcProfit()" required>
            @error('wholesale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">سعر البيع المقترح (د.ع) *</label>
            <input type="number" name="suggested_price" id="suggested_price"
                   class="form-control @error('suggested_price') is-invalid @enderror"
                   value="{{ old('suggested_price', 0) }}" min="0" oninput="calcProfit()" required>
            @error('suggested_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">أقل سعر بيع (د.ع) *</label>
            <input type="number" name="min_price" class="form-control @error('min_price') is-invalid @enderror"
                   value="{{ old('min_price', 0) }}" min="0" required>
            @error('min_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-bold">الكمية في المخزن *</label>
            <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                   value="{{ old('stock_quantity', 0) }}" min="0" required>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-0 p-3 h-100">
                <div class="small text-muted">ربح البائع المتوقع</div>
                <div class="fs-3 fw-bold text-success" id="profit-display">0 د.ع</div>
                <div class="text-muted" style="font-size:.72rem">= سعر البيع - سعر الجملة</div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" {{ old('is_featured') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="featured">منتج مميز (يظهر في السلايدر)</label>
            </div>
        </div>
    </div>
</div>

<div class="card table-card p-4 mb-4">
    <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-images me-1"></i> صور المنتج</h6>
    <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror"
           multiple accept="image/*" required onchange="previewImages(this)">
    <div class="text-muted small mt-1">يمكنك اختيار أكثر من صورة. الحجم الأقصى 3MB لكل صورة.</div>
    @error('images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

    <div id="image-preview" class="d-flex flex-wrap gap-2 mt-3"></div>

    <div class="mt-3 pt-3 border-top">
        <label class="form-label fw-bold"><i class="bi bi-youtube text-danger me-1"></i> رابط فيديو يوتيوب <small class="text-muted fw-normal">(اختياري)</small></label>
        <input type="url" name="youtube_url" id="youtubeUrl"
               class="form-control @error('youtube_url') is-invalid @enderror"
               value="{{ old('youtube_url') }}"
               placeholder="https://www.youtube.com/watch?v=..."
               oninput="previewYoutube(this.value)">
        @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="text-muted small mt-1">سيظهر الفيديو في معرض صور المنتج للمسوقين.</div>
        <div id="yt-preview" class="mt-2 d-none">
            <div class="position-relative rounded-3 overflow-hidden" style="width:200px;height:112px;">
                <img id="yt-thumb" src="" class="w-100 h-100" style="object-fit:cover;" alt="YouTube preview">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                     style="background:rgba(0,0,0,.35);">
                    <i class="bi bi-play-circle-fill text-white" style="font-size:2.5rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-check-circle me-1"></i> حفظ المنتج
    </button>
</div>

</form>
</div>
</div>
@endsection

@push('scripts')
<script>
function calcProfit() {
    const wholesale = parseInt(document.getElementById('wholesale_price').value) || 0;
    const suggested = parseInt(document.getElementById('suggested_price').value) || 0;
    const profit = Math.max(0, suggested - wholesale);
    document.getElementById('profit-display').textContent = profit.toLocaleString('ar-IQ') + ' د.ع';
    document.getElementById('profit-display').className = 'fs-3 fw-bold ' + (profit > 0 ? 'text-success' : 'text-danger');
}

function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    [...input.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style = 'width:80px;height:80px;object-fit:cover;border-radius:.5rem;border:2px solid #e2e8f0;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

function previewYoutube(url) {
    const preview = document.getElementById('yt-preview');
    const thumb   = document.getElementById('yt-thumb');
    const match   = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (match) {
        thumb.src = `https://img.youtube.com/vi/${match[1]}/hqdefault.jpg`;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
}
</script>
@endpush
