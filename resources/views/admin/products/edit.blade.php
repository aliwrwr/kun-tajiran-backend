@extends('admin.layouts.app')

@section('title', 'تعديل المنتج')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">تعديل المنتج</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
    </a>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">معلومات المنتج</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم المنتج (عربي) <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                               value="{{ old('name_ar', $product->name_ar) }}" required>
                        @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم المنتج (إنجليزي)</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $product->name) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description_ar" rows="4"
                                  class="form-control @error('description_ar') is-invalid @enderror">{{ old('description_ar', $product->description_ar) }}</textarea>
                        @error('description_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">سعر الجملة (د.ع) <span class="text-danger">*</span></label>
                            <input type="number" name="wholesale_price" id="wholesalePrice"
                                   class="form-control @error('wholesale_price') is-invalid @enderror"
                                   value="{{ old('wholesale_price', $product->wholesale_price) }}" min="0" required>
                            @error('wholesale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">سعر البيع (المقترح) <span class="text-danger">*</span></label>
                            <input type="number" name="suggested_price" id="suggestedPrice"
                                   class="form-control @error('suggested_price') is-invalid @enderror"
                                   value="{{ old('suggested_price', $product->suggested_price) }}" min="0" required>
                            @error('suggested_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحد الأدنى للبيع <span class="text-danger">*</span></label>
                            <input type="number" name="min_price" id="minPrice"
                                   class="form-control @error('min_price') is-invalid @enderror"
                                   value="{{ old('min_price', $product->min_price) }}" min="0" required>
                            @error('min_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الكمية في المخزن</label>
                            <input type="number" name="stock_quantity"
                                   class="form-control @error('stock_quantity') is-invalid @enderror"
                                   value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                            @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Live Profit Calculator -->
                    <div class="alert alert-success mt-3 mb-0" id="profitCalc">
                        <div class="d-flex justify-content-between">
                            <span>ربح المسوق المتوقع:</span>
                            <strong id="profitDisplay">0 د.ع</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category & Status -->
            <div class="card shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">التصنيف والحالة</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">التصنيف</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">بدون تصنيف</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="isActive" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">المنتج نشط</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                               id="isFeatured" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">منتج مميز</label>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="card shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">الصور</h6>

                    @if($product->images)
                        <div class="row g-2 mb-3" id="existingImages">
                            @foreach($product->images as $img)
                                <div class="col-6">
                                    <img src="{{ Storage::url($img) }}" class="img-fluid rounded-2" alt="صورة المنتج">
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mb-2">رفع صور جديدة سيستبدل الصور الحالية</small>
                    @endif

                    <input type="file" name="new_images[]" id="imagesInput" class="form-control" multiple accept="image/*">
                    <div class="mt-2 d-flex flex-wrap gap-2" id="imagePreview"></div>

                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label fw-semibold"><i class="bi bi-youtube text-danger me-1"></i> فيديو يوتيوب <small class="text-muted fw-normal">(اختياري)</small></label>
                        <input type="url" name="youtube_url" id="youtubeUrl"
                               class="form-control @error('youtube_url') is-invalid @enderror"
                               value="{{ old('youtube_url', $product->youtube_url) }}"
                               placeholder="https://www.youtube.com/watch?v=..."
                               oninput="previewYoutube(this.value)">
                        @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="yt-preview" class="mt-2 {{ $product->youtube_url ? '' : 'd-none' }}">
                            <div class="position-relative rounded-3 overflow-hidden" style="width:100%;max-width:200px;height:112px;">
                                <img id="yt-thumb" src="{{ $product->youtube_url ? 'https://img.youtube.com/vi/' . (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $product->youtube_url, $m) ? $m[1] : '') . '/hqdefault.jpg' : '' }}"
                                     class="w-100 h-100" style="object-fit:cover;" alt="YouTube preview">
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                     style="background:rgba(0,0,0,.35);">
                                    <i class="bi bi-play-circle-fill text-white" style="font-size:2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold">
                <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function calcProfit() {
    const wholesale = parseInt(document.getElementById('wholesalePrice').value) || 0;
    const suggested = parseInt(document.getElementById('suggestedPrice').value) || 0;
    const delivery  = parseInt(document.getElementById('deliveryFee').value) || 0;
    const profit = suggested - wholesale - delivery;
    document.getElementById('profitDisplay').textContent = profit + ' د.ع';
    document.getElementById('profitCalc').className = 'alert mt-3 mb-0 alert-' + (profit > 0 ? 'success' : 'danger');
}
['wholesalePrice','suggestedPrice','deliveryFee'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcProfit);
});
calcProfit();

document.getElementById('imagesInput').addEventListener('change', function () {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    [...this.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'rounded-2';
            img.style = 'width:60px;height:60px;object-fit:cover;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

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
