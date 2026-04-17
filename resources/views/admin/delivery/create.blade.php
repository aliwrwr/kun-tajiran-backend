@extends('admin.layouts.app')

@section('title', 'إضافة مندوب توصيل')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">إضافة مندوب توصيل جديد</h4>
    <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('admin.delivery.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold mb-3">بيانات الحساب</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required placeholder="اسم المندوب">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" required placeholder="07xxxxxxxxx">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               required placeholder="8 أحرف على الأقل">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">بيانات المندوب</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">المحافظة</label>
                        <select name="city" class="form-select @error('city') is-invalid @enderror">
                            <option value="">اختر المحافظة</option>
                            @foreach(['بغداد','البصرة','الموصل','أربيل','كركوك','النجف','كربلاء','الحلة','الناصرية','الديوانية','السماوة','الرمادي','الفلوجة','تكريت','بعقوبة','العمارة','الكوت','السليمانية','دهوك','ذي قار'] as $city)
                                <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">
                        <i class="bi bi-person-plus me-1"></i> إضافة المندوب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
