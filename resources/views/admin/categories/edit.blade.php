@extends('admin.layouts.app')
@section('title', 'تعديل القسم')
@section('page-title', 'تعديل القسم: ' . $category->name_ar)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold">تعديل بيانات القسم</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                                   value="{{ old('name', $category->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الاسم بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                   value="{{ old('name_ar', $category->name_ar) }}" required>
                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الأيقونة (Emoji)</label>
                            <input type="text" name="icon" class="form-control"
                                   value="{{ old('icon', $category->icon) }}" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', $category->sort_order) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الحالة</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">نشط</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">صورة القسم</label>
                            @if($category->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $category->image) }}" alt=""
                                         style="height:80px;border-radius:.5rem;object-fit:cover;">
                                    <div class="text-muted small mt-1">الصورة الحالية — قم برفع صورة جديدة للاستبدال</div>
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="image/*">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
