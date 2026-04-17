@php
    $perPageOptions = [5, 10, 50, 100, 500];
    $current        = (int) $paginator->perPage();
    $from           = $paginator->firstItem() ?? 0;
    $to             = $paginator->lastItem()  ?? 0;
    $total          = $paginator->total();

    // Keep all existing query params, strip page/per_page so links are clean
    $baseQuery = array_diff_key(request()->query(), ['page' => '', 'per_page' => '']);
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-top bg-white" style="border-radius:0 0 .75rem .75rem">

    {{-- Left: count info + pagination links --}}
    <div class="d-flex flex-column gap-1">
        <span class="text-muted" style="font-size:.78rem">
            عرض <strong>{{ $from }}</strong> – <strong>{{ $to }}</strong>
            من إجمالي <strong>{{ number_format($total) }}</strong>
        </span>
        @if($paginator->hasPages())
            {{ $paginator->links() }}
        @endif
    </div>

    {{-- Right: per-page switcher --}}
    <div class="d-flex align-items-center gap-1 flex-shrink-0">
        <span class="text-muted small me-1">عرض:</span>
        @foreach($perPageOptions as $opt)
            <a href="{{ request()->fullUrlWithQuery(array_merge($baseQuery, ['per_page' => $opt, 'page' => 1])) }}"
               class="btn btn-sm {{ $current === $opt ? 'btn-primary' : 'btn-outline-secondary' }} px-2 py-1"
               style="min-width:36px;font-size:.78rem">
                {{ $opt }}
            </a>
        @endforeach
    </div>

</div>

@once
@push('scripts')
<script>
(function () {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.addEventListener('change', function () {
            const all = document.querySelectorAll('.row-check');
            checkAll.checked = [...all].every(c => c.checked);
            checkAll.indeterminate = !checkAll.checked && [...all].some(c => c.checked);
        });
    });
})();
</script>
@endpush
@endonce
