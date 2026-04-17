@extends('admin.layouts.app')
@section('title', 'إضافة قسم جديد')
@section('page-title', 'إضافة قسم جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">بيانات القسم</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الاسم (English) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="مثال: Clothes" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الاسم بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                   value="{{ old('name_ar') }}" placeholder="مثال: ملابس" required>
                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الأيقونة (Emoji)</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon') }}" placeholder="مثال: 👗" maxlength="10">
                            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحالة</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">صورة القسم</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="image/*">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ القسم
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
