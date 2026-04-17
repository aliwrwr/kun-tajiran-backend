@extends('admin.layouts.app')
@section('title', 'تعديل بانر')

@section('content')
<div class="d-flex align-items-center mb-4 gap-2">
    <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-right"></i></a>
    <h4 class="fw-bold mb-0">تعديل البانر</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.banners._form', ['banner' => $banner])
            <button type="submit" class="btn btn-primary px-4">حفظ التعديلات</button>
        </form>
    </div>
</div>
@endsection
