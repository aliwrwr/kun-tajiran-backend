@extends('admin.layouts.app')
@section('title', 'أجور التوصيل')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">🚚 إدارة أجور التوصيل</h4>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="deliveryTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-600" id="zones-tab" data-bs-toggle="tab" data-bs-target="#zones-pane" type="button">
            <i class="bi bi-geo-alt-fill me-1"></i> المحافظات ({{ $zones->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-600" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers-pane" type="button">
            <i class="bi bi-tag-fill me-1"></i> عروض التوصيل ({{ $offers->count() }})
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ═══════════════════════ TAB 1: ZONES ═══════════════════════ --}}
    <div class="tab-pane fade show active" id="zones-pane">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">حدد أجرة التوصيل الأساسية لكل محافظة. يمكن تطبيق عروض خصم إضافية من تبويب العروض.</p>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('addZoneModal').querySelector('[data-bs-toggle]')?.click(); document.getElementById('addZoneModal').style.display='flex'" data-bs-toggle="modal" data-bs-target="#addZoneModal">
                    <i class="bi bi-plus-lg me-1"></i> إضافة محافظة
                </button>
                <button class="btn btn-success btn-sm" onclick="document.getElementById('bulkForm').submit()">
                    <i class="bi bi-save-fill me-1"></i> حفظ الكل
                </button>
            </div>
        </div>

        <form id="bulkForm" action="{{ route('admin.delivery-zones.bulk-update') }}" method="POST">
            @csrf
            @method('POST')
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>المحافظة</th>
                                <th style="width:200px">أجرة التوصيل (د.ع)</th>
                                <th style="width:100px" class="text-center">نشط</th>
                                <th style="width:120px" class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zones as $i => $zone)
                            <tr>
                                <td><span class="text-muted">{{ $i + 1 }}</span></td>
                                <td>
                                    <input type="hidden" name="zones[{{ $i }}][id]" value="{{ $zone->id }}">
                                    <strong>{{ $zone->province_name }}</strong>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="zones[{{ $i }}][fee]"
                                               value="{{ $zone->base_fee }}"
                                               class="form-control text-center"
                                               min="0" step="500" required>
                                        <span class="input-group-text">د.ع</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox"
                                               name="zones[{{ $i }}][active]" value="1"
                                               {{ $zone->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#editZoneModal{{ $zone->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.delivery-zones.destroy', $zone) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('حذف المحافظة؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-geo-alt fs-2 d-block mb-2"></i>
                                    لا توجد محافظات. أضف المحافظات أو استخدم زر "إضافة المحافظات العراقية".
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        @if($zones->isEmpty())
        <div class="text-center mt-3">
            <form action="{{ route('admin.delivery-zones.seed') }}" method="POST"
                  onsubmit="return confirm('سيتم إضافة المحافظات العراقية الـ 18 مع أجرة توصيل افتراضية. هل تريد المتابعة؟')">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-map-fill me-1"></i> إضافة المحافظات العراقية تلقائياً (18 محافظة)
                </button>
            </form>
        </div>
        @endif

    </div>

    {{-- ═══════════════════════ TAB 2: OFFERS ═══════════════════════ --}}
    <div class="tab-pane fade" id="offers-pane">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">إنشاء عروض خصم على أجور التوصيل. يمكن تطبيقها على جميع الباعة أو باعة محددين.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOfferModal">
                <i class="bi bi-plus-lg me-1"></i> إضافة عرض
            </button>
        </div>

        <div class="row g-3">
            @forelse($offers as $offer)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 border-0 {{ $offer->is_active ? '' : 'opacity-75' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0">{{ $offer->name }}</h6>
                            <span class="badge bg-{{ $offer->status_color }}">
                                {{ $offer->status_label }}
                            </span>
                        </div>

                        <div class="mb-2">
                            @if($offer->discount_type === 'free')
                                <span class="badge bg-success fs-6">توصيل مجاني 🎁</span>
                            @elseif($offer->discount_type === 'fixed')
                                <span class="badge bg-info fs-6">خصم ثابت: {{ number_format($offer->discount_value) }} د.ع</span>
                            @else
                                <span class="badge bg-warning text-dark fs-6">خصم {{ $offer->discount_value }}%</span>
                            @endif
                        </div>

                        <div class="small text-muted">
                            <div><i class="bi bi-people me-1"></i>
                                @if($offer->applies_to === 'all')
                                    يطبق على جميع الباعة
                                @else
                                    {{ $offer->sellers->count() }} بائع محدد
                                @endif
                            </div>
                            @if($offer->starts_at || $offer->ends_at)
                            <div class="mt-1">
                                <i class="bi bi-calendar-range me-1"></i>
                                {{ $offer->starts_at?->format('Y/m/d') ?? '∞' }}
                                —
                                {{ $offer->ends_at?->format('Y/m/d') ?? '∞' }}
                            </div>
                            @endif
                            @if($offer->applies_to === 'specific_sellers' && $offer->sellers->count() > 0)
                            <div class="mt-1">
                                <i class="bi bi-person-check me-1"></i>
                                {{ $offer->sellers->pluck('name')->join('، ') }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-transparent d-flex gap-2">
                        <form action="{{ route('admin.delivery-offers.toggle', $offer) }}" method="POST" class="flex-fill">
                            @csrf
                            <button class="btn btn-sm w-100 {{ $offer->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                {{ $offer->is_active ? 'تعطيل' : 'تفعيل' }}
                            </button>
                        </form>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editOfferModal{{ $offer->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('admin.delivery-offers.destroy', $offer) }}" method="POST"
                              onsubmit="return confirm('حذف العرض؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-tag fs-1 d-block mb-2"></i>
                لا توجد عروض. أضف أول عرض توصيل الآن.
            </div>
            @endforelse
        </div>

    </div>
</div>

{{-- ═══════════════ MODAL: Add Zone ═══════════════ --}}
<div class="modal fade" id="addZoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">إضافة محافظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery-zones.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">اسم المحافظة <span class="text-danger">*</span></label>
                        <input type="text" name="province_name" class="form-control" required placeholder="مثال: بغداد">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">أجرة التوصيل (د.ع) <span class="text-danger">*</span></label>
                        <input type="number" name="base_fee" class="form-control" required min="0" step="500" placeholder="5000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════ MODALS: Edit Zone ═══════════════ --}}
@foreach($zones as $zone)
<div class="modal fade" id="editZoneModal{{ $zone->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">تعديل: {{ $zone->province_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery-zones.update', $zone) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">اسم المحافظة</label>
                        <input type="text" name="province_name" class="form-control" value="{{ $zone->province_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">أجرة التوصيل (د.ع)</label>
                        <input type="number" name="base_fee" class="form-control" value="{{ $zone->base_fee }}" required min="0" step="500">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active{{ $zone->id }}" {{ $zone->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="active{{ $zone->id }}">نشط</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- ═══════════════ MODAL: Add Offer ═══════════════ --}}
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">إضافة عرض توصيل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery-offers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600">اسم العرض <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="مثال: عرض رمضان">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">نوع الخصم <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-select" required onchange="toggleDiscountValue(this, 'add')">
                                <option value="fixed">خصم ثابت (د.ع)</option>
                                <option value="percentage">خصم نسبة (%)</option>
                                <option value="free">توصيل مجاني</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="add-discount-value-group">
                            <label class="form-label fw-600">قيمة الخصم <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" class="form-control" min="0" step="1" placeholder="مثال: 2000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">يطبق على <span class="text-danger">*</span></label>
                            <select name="applies_to" class="form-select" required onchange="toggleSellers(this, 'add')">
                                <option value="all">جميع الباعة</option>
                                <option value="specific_sellers">باعة محددين</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add-is-active" checked>
                                <label class="form-check-label" for="add-is-active">تفعيل العرض فوراً</label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="add-sellers-group">
                            <label class="form-label fw-600">اختر الباعة</label>
                            <select name="seller_ids[]" class="form-select" multiple size="5">
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->name }} — {{ $seller->phone }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl (أو Cmd) لاختيار أكثر من بائع</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">تاريخ البداية</label>
                            <input type="date" name="starts_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">تاريخ الانتهاء</label>
                            <input type="date" name="ends_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة العرض</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════ MODALS: Edit Offer ═══════════════ --}}
@foreach($offers as $offer)
<div class="modal fade" id="editOfferModal{{ $offer->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">تعديل: {{ $offer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery-offers.update', $offer) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600">اسم العرض</label>
                            <input type="text" name="name" class="form-control" value="{{ $offer->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">نوع الخصم</label>
                            <select name="discount_type" class="form-select" required
                                    onchange="toggleDiscountValue(this, 'edit{{ $offer->id }}')">
                                <option value="fixed"      {{ $offer->discount_type === 'fixed'      ? 'selected' : '' }}>خصم ثابت (د.ع)</option>
                                <option value="percentage" {{ $offer->discount_type === 'percentage' ? 'selected' : '' }}>خصم نسبة (%)</option>
                                <option value="free"       {{ $offer->discount_type === 'free'       ? 'selected' : '' }}>توصيل مجاني</option>
                            </select>
                        </div>
                        <div class="col-md-6 {{ $offer->discount_type === 'free' ? 'd-none' : '' }}" id="edit{{ $offer->id }}-discount-value-group">
                            <label class="form-label fw-600">قيمة الخصم</label>
                            <input type="number" name="discount_value" class="form-control"
                                   value="{{ $offer->discount_value }}" min="0" step="1"
                                   {{ $offer->discount_type === 'free' ? '' : 'required' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">يطبق على</label>
                            <select name="applies_to" class="form-select" required
                                    onchange="toggleSellers(this, 'edit{{ $offer->id }}')">
                                <option value="all"              {{ $offer->applies_to === 'all'              ? 'selected' : '' }}>جميع الباعة</option>
                                <option value="specific_sellers" {{ $offer->applies_to === 'specific_sellers' ? 'selected' : '' }}>باعة محددين</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="edit-active-{{ $offer->id }}" {{ $offer->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit-active-{{ $offer->id }}">نشط</label>
                            </div>
                        </div>
                        <div class="col-12 {{ $offer->applies_to !== 'specific_sellers' ? 'd-none' : '' }}" id="edit{{ $offer->id }}-sellers-group">
                            <label class="form-label fw-600">اختر الباعة</label>
                            <select name="seller_ids[]" class="form-select" multiple size="5">
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}"
                                        {{ $offer->sellers->contains($seller->id) ? 'selected' : '' }}>
                                    {{ $seller->name }} — {{ $seller->phone }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اضغط Ctrl (أو Cmd) لاختيار أكثر من بائع</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">تاريخ البداية</label>
                            <input type="date" name="starts_at" class="form-control"
                                   value="{{ $offer->starts_at?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">تاريخ الانتهاء</label>
                            <input type="date" name="ends_at" class="form-control"
                                   value="{{ $offer->ends_at?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
function toggleDiscountValue(select, prefix) {
    const group = document.getElementById(prefix + '-discount-value-group');
    if (!group) return;
    if (select.value === 'free') {
        group.classList.add('d-none');
        const input = group.querySelector('input');
        if (input) { input.removeAttribute('required'); input.value = '0'; }
    } else {
        group.classList.remove('d-none');
        const input = group.querySelector('input');
        if (input) input.setAttribute('required', '');
    }
}

function toggleSellers(select, prefix) {
    const group = document.getElementById(prefix + '-sellers-group');
    if (!group) return;
    if (select.value === 'specific_sellers') {
        group.classList.remove('d-none');
    } else {
        group.classList.add('d-none');
    }
}
</script>

<style>
.fw-600 { font-weight: 600; }
.nav-tabs .nav-link { font-weight: 600; color: #475569; }
.nav-tabs .nav-link.active { color: #2563eb; border-bottom-color: #2563eb; }
</style>
@endsection
