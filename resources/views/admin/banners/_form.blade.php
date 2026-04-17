{{-- Shared form partial for create/edit --}}
<div class="row g-3 mb-4">
    {{-- Image Upload --}}
    <div class="col-12">
        <label class="form-label fw-semibold">صورة البانر <span class="text-danger">*</span></label>
        @if(isset($banner))
            <div class="mb-2">
                <img src="{{ asset('storage/' . $banner->image) }}" class="rounded" style="height:120px;object-fit:cover;" alt="">
            </div>
        @endif
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
               accept="image/*" {{ isset($banner) ? '' : 'required' }}
               onchange="previewImg(this)">
        <img id="imgPreview" class="mt-2 rounded d-none" style="height:100px;object-fit:cover;" alt="">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">الأبعاد المثالية: 1200×500 px. الحد الأقصى 3MB</small>
    </div>

    {{-- Title & Subtitle --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">العنوان (اختياري)</label>
        <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}"
               class="form-control" placeholder="مثال: عرض خاص">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">النص الفرعي (اختياري)</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle ?? '') }}"
               class="form-control" placeholder="مثال: خصم 20% على المنتجات">
    </div>

    {{-- Badge --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">نص الشارة (اختياري)</label>
        <input type="text" name="badge_text" value="{{ old('badge_text', $banner->badge_text ?? '') }}"
               class="form-control" placeholder="مثال: جديد">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">لون الشارة</label>
        <div class="input-group">
            <input type="color" name="badge_color" value="{{ old('badge_color', $banner->badge_color ?? '#FF6B35') }}"
                   class="form-control form-control-color">
            <input type="text" id="badgeColorText" value="{{ old('badge_color', $banner->badge_color ?? '#FF6B35') }}"
                   class="form-control" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">الترتيب</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
               class="form-control" min="0">
    </div>

    {{-- Link --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">نوع الرابط</label>
        <select name="link_type" class="form-select" id="linkTypeSelect" onchange="toggleLink()">
            <option value="none"    {{ old('link_type', $banner->link_type ?? 'none') === 'none'    ? 'selected' : '' }}>بدون رابط</option>
            <option value="product" {{ old('link_type', $banner->link_type ?? 'none') === 'product' ? 'selected' : '' }}>منتج محدد</option>
            <option value="url"     {{ old('link_type', $banner->link_type ?? 'none') === 'url'     ? 'selected' : '' }}>رابط خارجي</option>
        </select>
    </div>
    <div class="col-md-8" id="linkField" style="display:none">
        <label class="form-label fw-semibold" id="linkLabel">الرابط</label>
        <input type="text" name="link" value="{{ old('link', $banner->link ?? '') }}"
               class="form-control" placeholder="" id="linkInput">
        <small class="text-muted" id="linkHint"></small>
    </div>
</div>

<script>
document.querySelector('[name=badge_color]').addEventListener('input', function() {
    document.getElementById('badgeColorText').value = this.value;
});
function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('imgPreview');
            img.src = e.target.result;
            img.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleLink() {
    const type = document.getElementById('linkTypeSelect').value;
    const field = document.getElementById('linkField');
    const label = document.getElementById('linkLabel');
    const hint  = document.getElementById('linkHint');
    const input = document.getElementById('linkInput');
    if (type === 'none') {
        field.style.display = 'none';
    } else {
        field.style.display = '';
        if (type === 'product') {
            label.textContent = 'رقم المنتج (ID)';
            input.placeholder = 'مثال: 5';
            hint.textContent  = 'سيفتح التطبيق صفحة تفاصيل المنتج';
        } else {
            label.textContent = 'الرابط الخارجي';
            input.placeholder = 'https://...';
            hint.textContent  = '';
        }
    }
}
// Init on load
toggleLink();
</script>
