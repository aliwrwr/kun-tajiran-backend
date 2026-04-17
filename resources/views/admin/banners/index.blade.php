@extends('admin.layouts.app')
@section('title', 'البانرات الإعلانية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">🖼️ البانرات الإعلانية</h4>
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> إضافة بانر
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@if($banners->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-images fs-1"></i>
        <p class="mt-2">لا توجد بانرات بعد. أضف أول بانر الآن!</p>
    </div>
@else
<div class="row g-3">
    @foreach($banners as $banner)
    <div class="col-md-4">
        <div class="card shadow-sm h-100 {{ !$banner->is_active ? 'opacity-50' : '' }}">
            <div class="position-relative">
                <img src="{{ asset('storage/' . $banner->image) }}" class="card-img-top" style="height:180px;object-fit:cover;" alt="">
                @if($banner->badge_text)
                    <span class="position-absolute top-0 end-0 m-2 badge" style="background:{{ $banner->badge_color }};">{{ $banner->badge_text }}</span>
                @endif
                <span class="position-absolute top-0 start-0 m-2 badge {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $banner->is_active ? 'نشط' : 'مخفي' }}
                </span>
            </div>
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $banner->title ?? '—' }}</h6>
                <small class="text-muted">{{ $banner->subtitle ?? '' }}</small>
                @if($banner->link_type !== 'none' && $banner->link)
                    <div class="mt-1"><small class="text-info"><i class="bi bi-link-45deg"></i> {{ $banner->link_type === 'product' ? 'منتج #'.$banner->link : $banner->link }}</small></div>
                @endif
                <div class="mt-1"><small class="text-muted">الترتيب: {{ $banner->sort_order }}</small></div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary flex-fill">تعديل</a>
                <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm {{ $banner->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                        {{ $banner->is_active ? 'إخفاء' : 'تفعيل' }}
                    </button>
                </form>
                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('حذف البانر؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
