@extends('admin.layouts.app')
@section('title', 'ط§ظ„ط¥ط´ط¹ط§ط±ط§طھ')
@section('page-title', 'ط¥ط±ط³ط§ظ„ ط§ظ„ط¥ط´ط¹ط§ط±ط§طھ')

@section('content')

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- â•گâ•گâ•گ Send Form â•گâ•گâ•گ --}}
    <div class="col-xl-7">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3">
                <span class="bg-primary text-white rounded-2 p-1 lh-1"><i class="bi bi-bell-fill"></i></span>
                <h6 class="mb-0 fw-bold">ط¥ط±ط³ط§ظ„ ط¥ط´ط¹ط§ط± ط¬ط¯ظٹط¯</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.notifications.send') }}"
                      enctype="multipart/form-data" id="notifForm">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    {{-- Title --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ط¹ظ†ظˆط§ظ† ط§ظ„ط¥ط´ط¹ط§ط± <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="titleInput"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" maxlength="100"
                               placeholder="ظ…ط«ط§ظ„: ط¹ط±ط¶ ط®ط§طµ ظ„ظپطھط±ط© ظ…ط­ط¯ظˆط¯ط©!" required>
                        <div class="d-flex justify-content-between mt-1">
                            @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                            <span class="text-muted small ms-auto"><span id="titleCount">0</span>/100</span>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ظ†طµ ط§ظ„ط¥ط´ط¹ط§ط± <span class="text-danger">*</span></label>
                        <textarea name="body" id="bodyInput"
                                  class="form-control @error('body') is-invalid @enderror"
                                  rows="4" maxlength="500"
                                  placeholder="ط§ظƒطھط¨ ط±ط³ط§ظ„طھظƒ ظ‡ظ†ط§...">{{ old('body') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('body')<span class="text-danger small">{{ $message }}</span>@enderror
                            <span class="text-muted small ms-auto"><span id="bodyCount">0</span>/500</span>
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">طµظˆط±ط© ط§ظ„ط¥ط´ط¹ط§ط± <span class="text-muted fw-normal">(ط§ط®طھظٹط§ط±ظٹ)</span></label>
                        <div class="border rounded-3 p-3 text-center position-relative" id="imgDropZone"
                             style="border-style:dashed!important;cursor:pointer;min-height:90px">
                            <input type="file" name="image" id="imgInput" accept="image/*"
                                   class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer">
                            <div id="imgPlaceholder">
                                <i class="bi bi-image text-muted fs-3"></i>
                                <p class="text-muted small mb-0 mt-1">ط§ظ†ظ‚ط± ط£ظˆ ط§ط³ط­ط¨ طµظˆط±ط© (JPEG/PNG/WebPطŒ ط­ط¯ ط£ظ‚طµظ‰ 2MB)</p>
                            </div>
                            <div id="imgPreviewWrap" style="display:none">
                                <img id="imgPreview" src="" alt="" class="rounded-2" style="max-height:120px;max-width:100%">
                                <br><small class="text-muted" id="imgName"></small>
                            </div>
                        </div>
                        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Target --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ط§ظ„ظ…ط³طھظ‡ط¯ظپظˆظ† <span class="text-danger">*</span></label>
                        <div class="row g-2 mb-3">
                            <div class="col-auto">
                                <div class="form-check border rounded-3 px-3 py-2">
                                    <input class="form-check-input" type="radio" name="target_type"
                                           id="tAll" value="all"
                                           {{ old('target_type', 'all') === 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tAll">
                                        <i class="bi bi-people-fill text-primary me-1"></i>ط§ظ„ط¬ظ…ظٹط¹
                                        <span class="badge bg-primary ms-1">{{ $resellersCount + $deliveryCount }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="form-check border rounded-3 px-3 py-2">
                                    <input class="form-check-input" type="radio" name="target_type"
                                           id="tRole" value="role"
                                           {{ old('target_type') === 'role' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tRole">
                                        <i class="bi bi-person-badge me-1 text-info"></i>ظ…ط¬ظ…ظˆط¹ط©
                                    </label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="form-check border rounded-3 px-3 py-2">
                                    <input class="form-check-input" type="radio" name="target_type"
                                           id="tUser" value="user"
                                           {{ old('target_type') === 'user' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tUser">
                                        <i class="bi bi-person-check me-1 text-success"></i>ظ…ط³طھط®ط¯ظ… ظ…ط­ط¯ط¯
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Role picker --}}
                        <div id="roleSection" style="display:{{ old('target_type') === 'role' ? 'block' : 'none' }}">
                            <select name="target_role" class="form-select">
                                <option value="reseller" {{ old('target_role') === 'reseller' ? 'selected' : '' }}>
                                    ط§ظ„ط¨ط§ط¦ط¹ظˆظ† ({{ $resellersCount }} ظ…طھط§ط­)
                                </option>
                                <option value="delivery" {{ old('target_role') === 'delivery' ? 'selected' : '' }}>
                                    ط§ظ„ظ…ظ†ط§ط¯ظٹط¨ ({{ $deliveryCount }} ظ…طھط§ط­)
                                </option>
                            </select>
                        </div>

                        {{-- User picker --}}
                        <div id="userSection" style="display:{{ old('target_type') === 'user' ? 'block' : 'none' }}">
                            <select name="target_user_id" class="form-select">
                                <option value="">-- ط§ط®طھط± ظ…ط³طھط®ط¯ظ…ط§ظ‹ --</option>
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('target_user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->role === 'reseller' ? 'ط¨ط§ط¦ط¹' : 'ظ…ظ†ط¯ظˆط¨' }}) â€” {{ $u->phone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Click action --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">ط§ظ„ط±ط§ط¨ط· ط¹ظ†ط¯ ط§ظ„ط¶ط؛ط· <span class="text-muted fw-normal">(ط§ط®طھظٹط§ط±ظٹ)</span></label>
                        <div class="input-group">
                            <span class="input-group-text text-muted">/</span>
                            <input type="text" name="click_action" class="form-control"
                                   value="{{ old('click_action') }}"
                                   placeholder="orders ط£ظˆ products ط£ظˆ wallet">
                        </div>
                        <div class="text-muted small mt-1">ظٹظپطھط­ ط§ظ„طھط·ط¨ظٹظ‚ ط¹ظ„ظ‰ ظ‡ط°ظ‡ ط§ظ„ط´ط§ط´ط© ط¹ظ†ط¯ ط§ظ„ط¶ط؛ط· ط¹ظ„ظ‰ ط§ظ„ط¥ط´ط¹ط§ط±</div>
                    </div>

                    {{-- Preview --}}
                    <div class="bg-light rounded-3 p-3 mb-4 d-flex gap-3 align-items-start" id="previewBox">
                        <div class="bg-primary rounded-2 text-white p-2 lh-1 flex-shrink-0">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-dark small" id="previewTitle">ط¹ظ†ظˆط§ظ† ط§ظ„ط¥ط´ط¹ط§ط±</div>
                            <div class="text-secondary" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="previewBody">ظ†طµ ط§ظ„ط¥ط´ط¹ط§ط± ط³ظٹط¸ظ‡ط± ظ‡ظ†ط§...</div>
                        </div>
                        <img id="previewThumb" src="" alt="" class="rounded-2 flex-shrink-0"
                             style="width:44px;height:44px;object-fit:cover;display:none">
                    </div>

                    <button type="submit" class="btn btn-primary px-5 fw-bold"
                            onclick="return confirm('ظ‡ظ„ طھط±ظٹط¯ ط¥ط±ط³ط§ظ„ ظ‡ط°ط§ ط§ظ„ط¥ط´ط¹ط§ط±طں')">
                        <i class="bi bi-send-fill me-2"></i>ط¥ط±ط³ط§ظ„ ط§ظ„ط¥ط´ط¹ط§ط±
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- â•گâ•گâ•گ Stats + History â•گâ•گâ•گ --}}
    <div class="col-xl-5">

        {{-- Stats cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card shadow-sm text-center p-3 border-0">
                    <div class="fs-2 fw-black text-primary">{{ $resellersCount }}</div>
                    <div class="text-muted small">ط¨ط§ط¦ط¹ظˆظ† ظ…طھطµظ„ظˆظ†</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card shadow-sm text-center p-3 border-0">
                    <div class="fs-2 fw-black text-info">{{ $deliveryCount }}</div>
                    <div class="text-muted small">ظ…ظ†ط§ط¯ظٹط¨ ظ…طھطµظ„ظˆظ†</div>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                <span class="bg-secondary text-white rounded-2 p-1 lh-1"><i class="bi bi-clock-history"></i></span>
                <h6 class="mb-0 fw-bold">ط³ط¬ظ„ ط§ظ„ط¥ط´ط¹ط§ط±ط§طھ ط§ظ„ظ…ط±ط³ظ„ط©</h6>
            </div>
            <div class="card-body p-0">
                @forelse($notifications as $n)
                <div class="px-3 py-2 border-bottom d-flex gap-3 align-items-start">
                    @if($n->image_url)
                        <img src="{{ $n->image_url }}" alt=""
                             class="rounded-2 flex-shrink-0"
                             style="width:42px;height:42px;object-fit:cover">
                    @else
                        <div class="bg-light rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:42px;height:42px">
                            <i class="bi bi-bell text-muted"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-dark" style="font-size:13px">{{ $n->title }}</div>
                        <div class="text-muted text-truncate" style="font-size:12px">{{ $n->body }}</div>
                        <div class="mt-1 d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark border" style="font-size:10px">
                                <i class="bi bi-people me-1"></i>{{ $n->target_label }}
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10px">
                                {{ $n->sent_count }} ظˆطµظ„
                            </span>
                            @if($n->failed_count > 0)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:10px">
                                {{ $n->failed_count }} ظپط´ظ„
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-muted text-nowrap flex-shrink-0" style="font-size:11px">
                        {{ $n->sent_at?->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                    ظ„ظ… ظٹظڈط±ط³ظ„ ط£ظٹ ط¥ط´ط¹ط§ط± ط­طھظ‰ ط§ظ„ط¢ظ†
                </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script>
// Character counters
const titleInput = document.getElementById('titleInput');
const bodyInput  = document.getElementById('bodyInput');
const titleCount = document.getElementById('titleCount');
const bodyCount  = document.getElementById('bodyCount');
const previewTitle = document.getElementById('previewTitle');
const previewBody  = document.getElementById('previewBody');

function updateCounters() {
    const t = titleInput.value;
    const b = bodyInput.value;
    titleCount.textContent = t.length;
    bodyCount.textContent  = b.length;
    previewTitle.textContent = t || 'ط¹ظ†ظˆط§ظ† ط§ظ„ط¥ط´ط¹ط§ط±';
    previewBody.textContent  = b || 'ظ†طµ ط§ظ„ط¥ط´ط¹ط§ط± ط³ظٹط¸ظ‡ط± ظ‡ظ†ط§...';
}
titleInput.addEventListener('input', updateCounters);
bodyInput.addEventListener('input', updateCounters);
updateCounters();

// Target type toggle
document.querySelectorAll('input[name="target_type"]').forEach(function(r) {
    r.addEventListener('change', function() {
        document.getElementById('roleSection').style.display = this.value === 'role' ? 'block' : 'none';
        document.getElementById('userSection').style.display = this.value === 'user' ? 'block' : 'none';
    });
});

// Image preview
document.getElementById('imgInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    document.getElementById('imgPlaceholder').style.display = 'none';
    document.getElementById('imgPreviewWrap').style.display = 'block';
    document.getElementById('imgPreview').src = url;
    document.getElementById('imgName').textContent = file.name;
    // Show in notification preview
    const thumb = document.getElementById('previewThumb');
    thumb.src = url;
    thumb.style.display = 'block';
});
</script>
@endpush
@endsection
