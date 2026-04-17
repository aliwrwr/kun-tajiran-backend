@extends('admin.layouts.app')
@section('title', 'إعدادات الشاشة الرئيسية')

@push('styles')
<style>
/* ───── Section Accordion Items ───── */
.section-item { background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; margin-bottom:10px; overflow:hidden; transition:box-shadow .2s,border-color .2s; }
.section-item.sortable-ghost { opacity:.4; }
.section-item.sortable-chosen { box-shadow:0 4px 20px rgba(59,130,246,.15); border-color:#3b82f6; }
.section-item.section-hidden { opacity:.5; }
.section-item-header { display:flex; align-items:center; gap:10px; padding:14px 16px; cursor:pointer; user-select:none; transition:background .15s; }
.section-item-header:hover { background:#f8fafc; }
.section-info { flex:1; min-width:0; }
.section-name-display { font-weight:700; font-size:15px; color:#1e293b; display:block; }
.section-summary { font-size:11px; color:#94a3b8; display:block; margin-top:2px; }
.drag-handle { color:#94a3b8; font-size:20px; cursor:grab; flex-shrink:0; }
.section-chevron { color:#64748b; font-size:14px; transition:transform .25s; flex-shrink:0; }
.section-chevron.open { transform:rotate(180deg); }
.section-settings-body { border-top:1px solid #f1f5f9; background:#f8fafc; }
.settings-panel { padding:20px; }
.order-badge { width:26px; height:26px; border-radius:50%; background:#dbeafe; color:#3b82f6; font-size:11px; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; }
.add-section-btn { border:2px dashed #cbd5e1; border-radius:14px; padding:14px; text-align:center; cursor:pointer; transition:all .2s; color:#64748b; background:#fff; width:100%; font-size:14px; }
.add-section-btn:hover { border-color:#3b82f6; color:#3b82f6; background:#eff6ff; }
.add-section-option:hover { background:#f0f9ff !important; border-color:#3b82f6 !important; }
/* ───── View Mode Selector ───── */
.view-mode-options { display:flex; gap:10px; flex-wrap:wrap; }
.view-mode-option input[type=radio] { display:none; }
.view-mode-option label { display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 20px; border:2px solid #e2e8f0; border-radius:12px; cursor:pointer; transition:all .2s; min-width:88px; }
.view-mode-option label .vm-icon { font-size:22px; }
.view-mode-option label .vm-label { font-size:12px; font-weight:600; color:#64748b; }
.view-mode-option input[type=radio]:checked + label { border-color:#3b82f6; background:#eff6ff; }
.view-mode-option input[type=radio]:checked + label .vm-label { color:#3b82f6; }
/* ───── Card Style Picker ───── */
.card-style-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(135px,1fr)); gap:12px; }
.card-style-option input[type=radio] { display:none; }
.card-style-option label { display:block; border:2px solid #e2e8f0; border-radius:12px; overflow:hidden; cursor:pointer; transition:all .2s; }
.card-style-option input[type=radio]:checked + label { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.2); }
.card-style-preview { height:120px; position:relative; overflow:hidden; }
.card-style-label { padding:7px 10px; font-size:12px; font-weight:600; background:#fff; text-align:center; color:#374151; border-top:1px solid #f1f5f9; }
.card-style-option input[type=radio]:checked + label .card-style-label { color:#3b82f6; }
/* ── Product Previews ── */
.pv-standard,.pv-minimal,.pv-detailed,.pv-dark { display:flex; flex-direction:column; height:100%; }
.pv-standard .img,.pv-minimal .img,.pv-detailed .img { display:flex; align-items:center; justify-content:center; font-size:28px; }
.pv-standard .img { background:linear-gradient(135deg,#dbeafe,#bfdbfe); flex:1; } .pv-standard .info { padding:6px 8px; background:#fff; }
.pv-standard .pname { font-size:10px; font-weight:700; color:#1e293b; } .pv-standard .pbadge { display:inline-block; margin-top:3px; padding:1px 7px; background:#22c55e; color:#fff; border-radius:4px; font-size:9px; }
.pv-minimal .img { background:linear-gradient(135deg,#fce7f3,#fbcfe8); flex:1; } .pv-minimal .info { padding:6px 8px; background:#fff; border-top:2px solid #6366f1; }
.pv-minimal .pname { font-size:10px; font-weight:700; color:#1e293b; } .pv-minimal .pprice { font-size:9px; color:#6366f1; font-weight:700; } .pv-minimal .pbtn { margin-top:3px; background:#6366f1; color:#fff; border-radius:4px; padding:1px 6px; font-size:8px; display:inline-block; }
.pv-detailed .img { background:linear-gradient(135deg,#d1fae5,#a7f3d0); flex:0 0 48px; } .pv-detailed .info { padding:5px 7px; flex:1; background:#fff; }
.pv-detailed .pcat { font-size:8px; background:#f0fdf4; color:#166534; padding:1px 5px; border-radius:10px; display:inline-block; } .pv-detailed .pname { font-size:9px; font-weight:700; color:#111; margin:2px 0; } .pv-detailed .prow { display:flex; gap:4px; margin-top:2px; font-size:8px; color:#64748b; } .pv-detailed .pstock { font-size:8px; color:#22c55e; }
.pv-dark { background:#1a1a2e; } .pv-dark .img { background:linear-gradient(135deg,#312e81,#4c1d95); flex:1; } .pv-dark .info { padding:6px 8px; }
.pv-dark .pname { font-size:10px; font-weight:700; color:#e2e8f0; } .pv-dark .pbadge { display:inline-block; margin-top:3px; padding:1px 6px; background:#7c3aed; color:#e9d5ff; border-radius:4px; font-size:9px; }
.pv-compact { position:relative; background:linear-gradient(135deg,#7c3aed,#2563eb); height:100%; }
.pv-compact .emoji { position:absolute; top:50%; left:50%; transform:translate(-50%,-60%); font-size:36px; } .pv-compact .poverlay { position:absolute; bottom:0; left:0; right:0; padding:6px 8px; background:linear-gradient(transparent,rgba(0,0,0,.7)); }
.pv-compact .pname { font-size:10px; font-weight:700; color:#fff; } .pv-compact .pbadge { display:inline-block; padding:1px 6px; background:#f59e0b; color:#fff; border-radius:4px; font-size:9px; }
/* ── Category Previews ── */
.cv-chip,.cv-gradient { display:flex; gap:5px; padding:10px 8px; background:#f8fafc; flex-wrap:wrap; height:100%; align-content:flex-start; }
.cv-chip .citem,.cv-gradient .citem { display:flex; flex-direction:column; align-items:center; gap:2px; width:36px; } .cv-chip .cbox,.cv-gradient .cbox { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
.cv-chip .cbox { background:#eff6ff; } .cv-chip .clbl,.cv-gradient .clbl { font-size:6px; font-weight:600; color:#374151; text-align:center; }
.cv-card { display:flex; gap:5px; padding:10px 8px; background:#f8fafc; flex-wrap:wrap; height:100%; align-content:flex-start; } .cv-card .citem { width:52px; background:#fff; border-radius:7px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.cv-card .cimg { height:30px; background:linear-gradient(135deg,#bfdbfe,#ddd6fe); display:flex; align-items:center; justify-content:center; font-size:14px; } .cv-card .ctxt { padding:2px 4px; } .cv-card .cnm { font-size:7px; font-weight:700; color:#111; } .cv-card .ccnt { font-size:6px; color:#6366f1; }
.cv-list { display:flex; flex-direction:column; gap:3px; padding:6px 8px; background:#f8fafc; height:100%; } .cv-list .citem { display:flex; align-items:center; gap:5px; padding:4px 7px; background:#fff; border-radius:5px; }
.cv-list .cicon { font-size:13px; } .cv-list .cinfo { flex:1; } .cv-list .cnm { font-size:7px; font-weight:700; color:#111; } .cv-list .ccnt { font-size:6px; color:#94a3b8; } .cv-list .carrow { font-size:10px; color:#94a3b8; }
/* ── Circle Category Preview ── */
.cv-circle { display:flex; gap:6px; padding:10px 8px; background:#f8fafc; flex-wrap:wrap; height:100%; align-content:flex-start; }
.cv-circle .citem { display:flex; flex-direction:column; align-items:center; gap:2px; width:36px; }
.cv-circle .cbox { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; background:linear-gradient(135deg,#6366f1,#818cf8); }
.cv-circle .clbl { font-size:6px; font-weight:600; color:#374151; text-align:center; }
</style>
@endpush

@section('content')
@php
    $saved           = \App\Models\HomeSetting::getAllAsArray();
    $defaultCfg      = \App\Models\HomeSetting::defaults()['sections_config'];
    $sectionsConfig  = json_decode($saved['sections_config'] ?? $defaultCfg, true)
                       ?? json_decode($defaultCfg, true);
    $allDefs = [
        'banners'    => ['icon' => '🖼️', 'label' => 'سلايدر الإعلانات', 'type' => 'banners'],
        'featured'   => ['icon' => '🔥', 'label' => 'المنتجات المميزة',  'type' => 'products'],
        'categories' => ['icon' => '📦', 'label' => 'الأقسام',           'type' => 'categories'],
        'products'   => ['icon' => '🛍️', 'label' => 'جميع المنتجات',   'type' => 'products'],
    ];
    $gradColors = ['#6366f1','#ec4899','#f97316','#22c55e','#06b6d4','#eab308'];
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">🏠 إعدادات الشاشة الرئيسية</h4>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-images me-1"></i> إدارة البانرات
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.home-settings.update') }}" method="POST" id="settingsForm">
    @csrf @method('PUT')
    <input type="hidden" name="sections_config" id="sections_config_input">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header fw-bold bg-white border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-arrow-down-up text-primary"></i> ترتيب الأقسام وإعداداتها
            <small class="text-muted fw-normal ms-auto">اسحب لتغيير الترتيب • انقر لفتح الإعدادات</small>
        </div>
        <div class="card-body">

            <div id="sections-list">
            @foreach($sectionsConfig as $idx => $sec)
            @php
                $sKey     = $sec['key'] ?? 'products';
                $sType    = $sec['type'] ?? ($allDefs[$sKey]['type'] ?? 'products');
                $sDef     = $allDefs[$sKey] ?? ['icon' => ($sType==='category_products' ? '🗂️' : '📄'), 'label' => ($sec['title'] ?? $sKey), 'type' => $sType];
                $sVis     = $sec['visible'] ?? true;
                $sTitle   = $sec['title']   ?? $sDef['label'];
                $sCatId   = $sec['category_id']   ?? null;
                $sCatName = $sec['category_name'] ?? null;
                $sVM      = $sec['view_mode']  ?? ($sType === 'categories' ? 'horizontal' : 'grid');
                $sCS      = $sec['card_style'] ?? ($sType === 'categories' ? 'chip' : 'standard');
                $sCnt     = $sec['count']      ?? 10;
                $sAP      = $sec['auto_play']  ?? true;
                $sDur     = $sec['duration']   ?? 4;
                $vmLabels = ['grid'=>'شبكي','list'=>'قائمة','horizontal'=>'أفقي'];
                $summary  = ($sType === 'products')
                    ? (($vmLabels[$sVM] ?? $sVM) . ' • ' . $sCS)
                    : ($sType === 'category_products'
                        ? (($sCatName ?? ('فئة #'.$sCatId)) . ' • ' . ($vmLabels[$sVM] ?? $sVM))
                        : ($sType === 'categories' ? (($vmLabels[$sVM] ?? $sVM) . ' • ' . $sCS) : 'بانر'));
            @endphp
            <div class="section-item {{ !$sVis ? 'section-hidden' : '' }}" data-key="{{ $sKey }}" data-type="{{ $sType }}" id="si-{{ $idx }}">
                <div class="section-item-header" onclick="toggleSection({{ $idx }})">
                    <i class="bi bi-grip-vertical drag-handle" onclick="event.stopPropagation()"></i>
                    <span style="font-size:22px;flex-shrink:0">{{ $sDef['icon'] }}</span>
                    <div class="section-info">
                        <span class="section-name-display" id="td-{{ $idx }}">{{ $sTitle }}</span>
                        <span class="section-summary">{{ $summary }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation()">
                        <div class="form-check form-switch mb-0" title="إظهار / إخفاء">
                            <input class="form-check-input section-visible-toggle" type="checkbox"
                                   id="vis-{{ $idx }}" {{ $sVis ? 'checked' : '' }}
                                   onchange="toggleHidden('si-{{ $idx }}',this.checked)">
                        </div>
                        <span class="order-badge" id="badge-{{ $idx }}">{{ $idx+1 }}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                onclick="event.stopPropagation();removeSection('si-{{ $idx }}')" title="إزالة القسم">
                            <i class="bi bi-trash"></i>
                        </button>
                        <i class="bi bi-chevron-down section-chevron" id="chev-{{ $idx }}"></i>
                    </div>
                </div>
                <!-- ── Settings Panel ── -->
                <div class="section-settings-body collapse" id="sb-{{ $idx }}">
                    <div class="settings-panel">

                        {{-- Title + Count/Duration row --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">اسم القسم في التطبيق</label>
                                <input type="text" class="form-control section-title-input"
                                       value="{{ $sTitle }}"
                                       oninput="document.getElementById('td-{{ $idx }}').textContent=this.value||'(بدون عنوان)'">
                            </div>
                            @if($sType === 'products' || $sType === 'category_products')
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small mb-1">عدد المنتجات</label>
                                <input type="number" class="form-control section-count" value="{{ $sCnt }}" min="1" max="30">
                            </div>
                            @elseif($sKey === 'categories')
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small mb-1">عدد الأقسام</label>
                                <input type="number" class="form-control section-count" value="{{ $sCnt }}" min="1" max="20">
                            </div>
                            @elseif($sKey === 'banners')
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small mb-1">مدة البانر (ثانية)</label>
                                <input type="number" class="form-control section-duration" value="{{ $sDur }}" min="2" max="15">
                            </div>
                            @endif
                        </div>

                        @if($sKey === 'banners')
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input section-auto-play" type="checkbox"
                                       id="ap-{{ $idx }}" {{ $sAP ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="ap-{{ $idx }}">تشغيل تلقائي للبانرات</label>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">لإضافة وتعديل البانرات</small>
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-images me-1"></i> إدارة البانرات
                            </a>
                        </div>
                        @endif

                        @if($sType === 'products')
                        {{-- View Mode --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                            <div class="view-mode-options">
                                @foreach([['grid','⊞','شبكي'],['list','☰','قائمة'],['horizontal','⇆','أفقي']] as [$val,$ico,$lbl])
                                <div class="view-mode-option">
                                    <input type="radio" name="vm_{{ $idx }}" id="vm_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-view-mode" {{ $sVM===$val?'checked':'' }}>
                                    <label for="vm_{{ $val }}_{{ $idx }}">
                                        <span class="vm-icon">{{ $ico }}</span>
                                        <span class="vm-label">{{ $lbl }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Product Card Style --}}
                        <div class="mb-2">
                            <label class="form-label fw-semibold small mb-2">تصميم بطاقة المنتج</label>
                            <div class="card-style-grid">
                                @foreach([
                                    ['standard','كلاسيكي','pv-standard','🛍️'],
                                    ['minimal','مبسط','pv-minimal','✨'],
                                    ['detailed','تفصيلي','pv-detailed','📦'],
                                    ['dark','داكن','pv-dark','🌙'],
                                    ['compact','مضغوط','pv-compact','🚀'],
                                ] as [$val,$lbl,$pvCls,$emoji])
                                <div class="card-style-option">
                                    <input type="radio" name="cs_{{ $idx }}" id="cs_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-card-style" {{ $sCS===$val?'checked':'' }}>
                                    <label for="cs_{{ $val }}_{{ $idx }}">
                                        <div class="card-style-preview {{ $pvCls }}">
                                            @if($pvCls==='pv-standard')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @elseif($pvCls==='pv-minimal')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><div class="pprice">15,000 د.ع</div><span class="pbtn">اطلب</span></div>
                                            @elseif($pvCls==='pv-detailed')
                                                <div class="img">{{ $emoji }}</div><div class="info"><span class="pcat">قسم</span><div class="pname">اسم المنتج</div><div class="prow"><span>15,000</span><span>3,000</span></div><div class="pstock">✔ متوفر</div></div>
                                            @elseif($pvCls==='pv-dark')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @elseif($pvCls==='pv-compact')
                                                <span class="emoji">{{ $emoji }}</span><div class="poverlay"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @endif
                                        </div>
                                        <div class="card-style-label">{{ $lbl }}</div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($sKey === 'categories')
                        {{-- Category View Mode --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                            <div class="view-mode-options">
                                @foreach([['grid','⊞','شبكي'],['horizontal','⇆','أفقي'],['list','☰','عمودي']] as [$val,$ico,$lbl])
                                <div class="view-mode-option">
                                    <input type="radio" name="vm_{{ $idx }}" id="vm_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-view-mode" {{ $sVM===$val?'checked':'' }}>
                                    <label for="vm_{{ $val }}_{{ $idx }}">
                                        <span class="vm-icon">{{ $ico }}</span>
                                        <span class="vm-label">{{ $lbl }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Category Card Style --}}
                        <div class="mb-2">
                            <label class="form-label fw-semibold small mb-2">تصميم بطاقة القسم</label>
                            <div class="card-style-grid">
                                @foreach([
                                    ['chip','شرائح','cv-chip'],
                                    ['card','بطاقات','cv-card'],
                                    ['gradient','تدرج لوني','cv-gradient'],
                                    ['list','قائمة','cv-list'],
                                    ['circle','دائري','cv-circle'],
                                ] as [$val,$lbl,$pvCls])
                                <div class="card-style-option">
                                    <input type="radio" name="cs_{{ $idx }}" id="cs_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-card-style" {{ $sCS===$val?'checked':'' }}>
                                    <label for="cs_{{ $val }}_{{ $idx }}">
                                        <div class="card-style-preview {{ $pvCls }}">
                                            @if($pvCls==='cv-chip')
                                                @foreach(['📱','👗','🍕','💄','🎮','🏠'] as $e)<div class="citem"><div class="cbox">{{ $e }}</div><div class="clbl">قسم</div></div>@endforeach
                                            @elseif($pvCls==='cv-gradient')
                                                @foreach(['📱','👗','🍕','💄','🎮','🏠'] as $i=>$e)<div class="citem"><div class="cbox" style="background:{{ $gradColors[$i%count($gradColors)] }}">{{ $e }}</div><div class="clbl">قسم</div></div>@endforeach
                                            @elseif($pvCls==='cv-card')
                                                @foreach(['📱','👗','🍕','🎮'] as $e)<div class="citem"><div class="cimg">{{ $e }}</div><div class="ctxt"><div class="cnm">قسم</div><div class="ccnt">12 منتج</div></div></div>@endforeach
                                            @elseif($pvCls==='cv-list')
                                                @foreach([['📱','إلكترونيات'],['👗','ملابس'],['🍕','طعام'],['🎮','ألعاب']] as [$e,$n])<div class="citem"><span class="cicon">{{ $e }}</span><div class="cinfo"><div class="cnm">{{ $n }}</div><div class="ccnt">12 منتج</div></div><span class="carrow">›</span></div>@endforeach
                                            @elseif($pvCls==='cv-circle')
                                                @foreach(['📱','👗','🍕','💄','🎮','🏠'] as $e)<div class="citem"><div class="cbox">{{ $e }}</div><div class="clbl">قسم</div></div>@endforeach
                                            @endif
                                        </div>
                                        <div class="card-style-label">{{ $lbl }}</div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($sType === 'category_products')
                        {{-- Category badge (read-only) + View Mode + Card Style --}}
                        <input type="hidden" class="section-category-id" value="{{ $sCatId }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small mb-1">الفئة المرتبطة</label>
                            <div class="d-flex align-items-center gap-2 p-2 rounded border" style="background:#eff6ff;border-color:#bfdbfe!important">
                                <span class="fs-5">🗂️</span>
                                <span class="fw-bold text-primary section-category-name">{{ $sCatName ?? 'فئة #'.$sCatId }}</span>
                                <small class="text-muted ms-auto">لتغيير الفئة: احذف القسم وأضف جديداً</small>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                            <div class="view-mode-options">
                                @foreach([['grid','⊞','شبكي'],['list','☰','قائمة'],['horizontal','⇆','أفقي']] as [$val,$ico,$lbl])
                                <div class="view-mode-option">
                                    <input type="radio" name="vm_{{ $idx }}" id="vm_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-view-mode" {{ $sVM===$val?'checked':'' }}>
                                    <label for="vm_{{ $val }}_{{ $idx }}">
                                        <span class="vm-icon">{{ $ico }}</span>
                                        <span class="vm-label">{{ $lbl }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small mb-2">تصميم بطاقة المنتج</label>
                            <div class="card-style-grid">
                                @foreach([
                                    ['standard','كلاسيكي','pv-standard','🛍️'],
                                    ['minimal','مبسط','pv-minimal','✨'],
                                    ['detailed','تفصيلي','pv-detailed','📦'],
                                    ['dark','داكن','pv-dark','🌙'],
                                    ['compact','مضغوط','pv-compact','🚀'],
                                ] as [$val,$lbl,$pvCls,$emoji])
                                <div class="card-style-option">
                                    <input type="radio" name="cs_{{ $idx }}" id="cs_{{ $val }}_{{ $idx }}"
                                           value="{{ $val }}" class="section-card-style" {{ $sCS===$val?'checked':'' }}>
                                    <label for="cs_{{ $val }}_{{ $idx }}">
                                        <div class="card-style-preview {{ $pvCls }}">
                                            @if($pvCls==='pv-standard')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @elseif($pvCls==='pv-minimal')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><div class="pprice">15,000 د.ع</div><span class="pbtn">اطلب</span></div>
                                            @elseif($pvCls==='pv-detailed')
                                                <div class="img">{{ $emoji }}</div><div class="info"><span class="pcat">قسم</span><div class="pname">اسم المنتج</div><div class="prow"><span>15,000</span><span>3,000</span></div><div class="pstock">✔ متوفر</div></div>
                                            @elseif($pvCls==='pv-dark')
                                                <div class="img">{{ $emoji }}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @elseif($pvCls==='pv-compact')
                                                <span class="emoji">{{ $emoji }}</span><div class="poverlay"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>
                                            @endif
                                        </div>
                                        <div class="card-style-label">{{ $lbl }}</div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            @endforeach
            </div>{{-- #sections-list --}}

            <button type="button" class="add-section-btn mt-2"
                    data-bs-toggle="modal" data-bs-target="#addSectionModal">
                <i class="bi bi-plus-circle me-1"></i> إضافة قسم
            </button>

        </div>
    </div>

    <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
            <i class="bi bi-save me-2"></i> حفظ الإعدادات
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">إلغاء</a>
    </div>
</form>

<!-- ── Add Section Modal ─────────────────────────────────────────────────── -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-primary"></i> إضافة قسم جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">

                {{-- Fixed sections --}}
                <p class="text-muted small fw-semibold mb-2" style="letter-spacing:.5px;text-transform:uppercase">أقسام ثابتة</p>
                <div id="available-sections-body"><!-- filled by JS --></div>

                <hr class="my-4">

                {{-- Custom category products section --}}
                <p class="text-muted small fw-semibold mb-3" style="letter-spacing:.5px;text-transform:uppercase">قسم منتجات مخصص</p>
                <div class="border rounded-3 overflow-hidden shadow-sm">
                    {{-- Collapsible header --}}
                    <div class="p-3 d-flex align-items-center gap-3" onclick="toggleCustomCreator()"
                         style="cursor:pointer;background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%)">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:46px;height:46px;background:linear-gradient(135deg,#3b82f6,#6366f1)">
                            <i class="bi bi-grid-3x3-gap-fill text-white fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-6">قسم منتجات بفئة محددة</div>
                            <small class="text-muted">اختر فئة وخصّص تصميمها على الشاشة الرئيسية</small>
                        </div>
                        <i class="bi bi-chevron-up text-primary fs-5" id="creator-chev" style="transition:transform .25s"></i>
                    </div>
                    {{-- Form body (starts open) --}}
                    <div id="custom-creator-body">
                        <div class="p-4 border-top">
                            {{-- Section name --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1">
                                    <i class="bi bi-type me-1 text-primary"></i> اسم القسم في التطبيق <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="cc-title"
                                       placeholder="مثال: أحدث الإلكترونيات، عروض الملابس…">
                            </div>
                            {{-- Category select --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1">
                                    <i class="bi bi-tags me-1 text-primary"></i> الفئة <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="cc-category">
                                    <option value="">-- جاري التحميل... --</option>
                                </select>
                                <div id="cc-cat-loading" class="text-muted small mt-1 d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> جاري تحميل الفئات...
                                </div>
                            </div>
                            {{-- View mode --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-2">
                                    <i class="bi bi-layout-three-columns me-1 text-primary"></i> طريقة العرض
                                </label>
                                <div class="view-mode-options">
                                    <div class="view-mode-option">
                                        <input type="radio" name="cc_vm" id="cc_vm_grid" value="grid">
                                        <label for="cc_vm_grid"><span class="vm-icon">⊞</span><span class="vm-label">شبكي</span></label>
                                    </div>
                                    <div class="view-mode-option">
                                        <input type="radio" name="cc_vm" id="cc_vm_horizontal" value="horizontal" checked>
                                        <label for="cc_vm_horizontal"><span class="vm-icon">⇆</span><span class="vm-label">أفقي</span></label>
                                    </div>
                                    <div class="view-mode-option">
                                        <input type="radio" name="cc_vm" id="cc_vm_list" value="list">
                                        <label for="cc_vm_list"><span class="vm-icon">☰</span><span class="vm-label">قائمة</span></label>
                                    </div>
                                </div>
                            </div>
                            {{-- Card style (filled by JS) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-2">
                                    <i class="bi bi-card-heading me-1 text-primary"></i> تصميم بطاقة المنتج
                                </label>
                                <div class="card-style-grid" id="cc-card-style-grid"><!-- filled by JS --></div>
                            </div>
                            {{-- Count --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold small mb-1">
                                    <i class="bi bi-hash me-1 text-primary"></i> عدد المنتجات المعروضة:
                                    <span class="badge bg-primary ms-1" id="cc-count-display">10</span>
                                </label>
                                <input type="range" class="form-range" id="cc-count" min="4" max="30" value="10"
                                       oninput="document.getElementById('cc-count-display').textContent=this.value">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">4</small>
                                    <small class="text-muted">30</small>
                                </div>
                            </div>
                            {{-- Submit --}}
                            <button type="button" class="btn btn-primary w-100 py-2 fw-bold rounded-3"
                                    onclick="addCustomCategorySection()">
                                <i class="bi bi-plus-lg me-2"></i> إضافة هذا القسم إلى الشاشة الرئيسية
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    /* ── Section definitions (for add-modal & new-item HTML generation) ── */
    const DEFS = {
        banners:    { icon: '🖼️', label: 'سلايدر الإعلانات', type: 'banners' },
        featured:   { icon: '🔥', label: 'المنتجات المميزة',  type: 'products' },
        categories: { icon: '📦', label: 'الأقسام',           type: 'categories' },
        products:   { icon: '🛍️', label: 'جميع المنتجات',   type: 'products' },
    };
    const GC = ['#6366f1','#ec4899','#f97316','#22c55e','#06b6d4','#eab308'];
    let idCtr = {{ count($sectionsConfig ?? []) }};

    const listEl = document.getElementById('sections-list');

    /* ── SortableJS ── */
    Sortable.create(listEl, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: rebadge,
    });

    function rebadge() {
        listEl.querySelectorAll('.section-item').forEach((el, i) => {
            const b = el.querySelector('.order-badge');
            if (b) b.textContent = i + 1;
        });
    }

    /* ── Accordion toggle ── */
    window.toggleSection = function (idx) {
        const body  = document.getElementById('sb-' + idx);
        const chev  = document.getElementById('chev-' + idx);
        if (!body) return;
        body.classList.toggle('show');
        if (chev) chev.classList.toggle('open');
    };

    /* ── Visibility toggle ── */
    window.toggleHidden = function (itemId, visible) {
        const el = document.getElementById(itemId);
        if (el) el.classList.toggle('section-hidden', !visible);
    };

    /* ── Remove section ── */
    window.removeSection = function (itemId) {
        if (!confirm('هل تريد إزالة هذا القسم من الترتيب؟')) return;
        const el = document.getElementById(itemId);
        if (!el) return;
        el.style.transition = 'opacity .2s,transform .2s';
        el.style.opacity = '0'; el.style.transform = 'scale(.97)';
        setTimeout(() => { el.remove(); rebadge(); refreshModal(); }, 220);
    };

    /* ── Add section modal ── */
    function refreshModal() {
        const existing = Array.from(listEl.querySelectorAll('.section-item')).map(el => el.dataset.key);
        const avail = Object.keys(DEFS).filter(k => !existing.includes(k));
        const body = document.getElementById('available-sections-body');
        if (!body) return;
        if (!avail.length) {
            body.innerHTML = '<p class="text-center text-muted py-3 mb-0">جميع الأقسام المتاحة مضافة بالفعل</p>';
            return;
        }
        body.innerHTML = avail.map(k => {
            const d = DEFS[k];
            return `<div class="d-flex align-items-center gap-3 p-3 border rounded mb-2 add-section-option"
                         style="cursor:pointer;transition:all .15s" onclick="addSection('${k}')">
                <span style="font-size:26px">${d.icon}</span>
                <div>
                    <div class="fw-bold">${d.label}</div>
                    <small class="text-muted">${d.type==='products'?'قسم منتجات':d.type==='categories'?'قسم أقسام':'إعلانات'}</small>
                </div>
                <i class="bi bi-plus-circle text-primary ms-auto fs-5"></i>
            </div>`;
        }).join('');
    }
    document.getElementById('addSectionModal').addEventListener('show.bs.modal', () => {
        refreshModal();
        loadCategories();
    });

    window.addSection = function (key) {
        bootstrap.Modal.getInstance(document.getElementById('addSectionModal'))?.hide();
        const def = DEFS[key];
        const idx = idCtr++;
        const id  = 'si-' + idx;
        const defaults = {
            banners:    { title: 'عروض حصرية',       type: 'banners',     auto_play: true, duration: 4 },
            featured:   { title: '🔥 منتجات مميزة',  type: 'products',    view_mode: 'horizontal', card_style: 'standard', count: 6 },
            categories: { title: '📦 الأقسام',        type: 'categories',  view_mode: 'horizontal', card_style: 'chip', count: 8 },
            products:   { title: '🛍️ جميع المنتجات', type: 'products',    view_mode: 'grid', card_style: 'standard', count: 10 },
        };
        const d = defaults[key] || defaults.products;
        listEl.insertAdjacentHTML('beforeend', buildItemHTML(key, def, idx, id, d));
        rebadge();
        refreshModal();
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    /* ── Shared option arrays ── */
    const vmOpts = [['grid','⊞','شبكي'],['list','☰','قائمة'],['horizontal','⇆','أفقي']];
    const psOpts = [['standard','كلاسيكي','pv-standard','🛍️'],['minimal','مبسط','pv-minimal','✨'],
                    ['detailed','تفصيلي','pv-detailed','📦'],['dark','داكن','pv-dark','🌙'],['compact','مضغوط','pv-compact','🚀']];
    const csOpts = [['chip','شرائح','cv-chip'],['card','بطاقات','cv-card'],
                    ['gradient','تدرج لوني','cv-gradient'],['list','قائمة','cv-list'],['circle','دائري','cv-circle']];

    /* ── Build HTML for a new section item ── */
    function buildItemHTML(key, def, idx, id, d) {
        const type = def.type;

        let panel = `
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small mb-1">اسم القسم في التطبيق</label>
                <input type="text" class="form-control section-title-input" value="${esc(d.title)}"
                       oninput="document.getElementById('td-${idx}').textContent=this.value||'(بدون عنوان)'">
            </div>
            ${(type==='products'||type==='category_products') ? `<div class="col-md-3"><label class="form-label fw-semibold small mb-1">عدد المنتجات</label><input type="number" class="form-control section-count" value="${d.count||10}" min="1" max="30"></div>` : ''}
            ${key==='categories' ? `<div class="col-md-3"><label class="form-label fw-semibold small mb-1">عدد الأقسام</label><input type="number" class="form-control section-count" value="${d.count||8}" min="1" max="20"></div>` : ''}
            ${key==='banners'    ? `<div class="col-md-3"><label class="form-label fw-semibold small mb-1">مدة البانر (ثانية)</label><input type="number" class="form-control section-duration" value="${d.duration||4}" min="2" max="15"></div>` : ''}
        </div>`;

        if (key === 'banners') {
            panel += `<div class="mb-4"><div class="form-check form-switch">
                <input class="form-check-input section-auto-play" type="checkbox" id="ap-${idx}" ${d.auto_play!==false?'checked':''}>
                <label class="form-check-label fw-semibold" for="ap-${idx}">تشغيل تلقائي للبانرات</label>
            </div></div>`;
        }

        if (type === 'products') {
            panel += `<div class="mb-4"><label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                <div class="view-mode-options">${vmOpts.map(([v,ic,lb])=>`
                <div class="view-mode-option">
                    <input type="radio" name="vm_${idx}" id="vm_${v}_${idx}" value="${v}" class="section-view-mode" ${d.view_mode===v?'checked':''}>
                    <label for="vm_${v}_${idx}"><span class="vm-icon">${ic}</span><span class="vm-label">${lb}</span></label>
                </div>`).join('')}</div></div>`;

            panel += `<div class="mb-2"><label class="form-label fw-semibold small mb-2">تصميم بطاقة المنتج</label>
                <div class="card-style-grid">${psOpts.map(([v,lb,pc,em])=>`
                <div class="card-style-option">
                    <input type="radio" name="cs_${idx}" id="cs_${v}_${idx}" value="${v}" class="section-card-style" ${d.card_style===v?'checked':''}>
                    <label for="cs_${v}_${idx}">
                        <div class="card-style-preview ${pc}">${productPreview(pc,em)}</div>
                        <div class="card-style-label">${lb}</div>
                    </label>
                </div>`).join('')}</div></div>`;
        }

        if (key === 'categories') {
            panel += `<div class="mb-4"><label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                <div class="view-mode-options">${[['grid','⊞','شبكي'],['horizontal','⇆','أفقي'],['list','☰','عمودي']].map(([v,ic,lb])=>`
                <div class="view-mode-option">
                    <input type="radio" name="vm_${idx}" id="vm_${v}_${idx}" value="${v}" class="section-view-mode" ${(d.view_mode||'horizontal')===v?'checked':''}>
                    <label for="vm_${v}_${idx}"><span class="vm-icon">${ic}</span><span class="vm-label">${lb}</span></label>
                </div>`).join('')}</div></div>`;
            panel += `<div class="mb-2"><label class="form-label fw-semibold small mb-2">تصميم بطاقة القسم</label>
                <div class="card-style-grid">${csOpts.map(([v,lb,pc])=>`
                <div class="card-style-option">
                    <input type="radio" name="cs_${idx}" id="cs_${v}_${idx}" value="${v}" class="section-card-style" ${d.card_style===v?'checked':''}>
                    <label for="cs_${v}_${idx}">
                        <div class="card-style-preview ${pc}">${catPreview(pc)}</div>
                        <div class="card-style-label">${lb}</div>
                    </label>
                </div>`).join('')}</div></div>`;
        }

        if (type === 'category_products') {
            panel += `
            <input type="hidden" class="section-category-id" value="${d.category_id||''}">            <div class="mb-3">
                <label class="form-label fw-semibold small mb-1">الفئة المرتبطة</label>
                <div class="d-flex align-items-center gap-2 p-2 rounded border" style="background:#eff6ff;border-color:#bfdbfe!important">
                    <span class="fs-5">🗂️</span>
                    <span class="fw-bold text-primary section-category-name">${esc(d.category_name||'فئة #'+d.category_id)}</span>
                    <small class="text-muted ms-auto">لتغيير الفئة: احذف القسم وأضف جديداً</small>
                </div>
            </div>`;
            panel += `<div class="mb-4"><label class="form-label fw-semibold small mb-2">طريقة العرض</label>
                <div class="view-mode-options">${vmOpts.map(([v,ic,lb])=>`
                <div class="view-mode-option">
                    <input type="radio" name="vm_${idx}" id="vm_${v}_${idx}" value="${v}" class="section-view-mode" ${(d.view_mode||'horizontal')===v?'checked':''}>
                    <label for="vm_${v}_${idx}"><span class="vm-icon">${ic}</span><span class="vm-label">${lb}</span></label>
                </div>`).join('')}</div></div>`;
            panel += `<div class="mb-2"><label class="form-label fw-semibold small mb-2">تصميم بطاقة المنتج</label>
                <div class="card-style-grid">${psOpts.map(([v,lb,pc,em])=>`
                <div class="card-style-option">
                    <input type="radio" name="cs_${idx}" id="cs_${v}_${idx}" value="${v}" class="section-card-style" ${d.card_style===v?'checked':''}>
                    <label for="cs_${v}_${idx}">
                        <div class="card-style-preview ${pc}">${productPreview(pc,em)}</div>
                        <div class="card-style-label">${lb}</div>
                    </label>
                </div>`).join('')}</div></div>`;
        }

        const summary = type==='products' ? `${d.view_mode||'grid'} • ${d.card_style||'standard'}`
                      : type==='category_products' ? `${esc(d.category_name||'')} • ${d.view_mode||'horizontal'}`
                      : key==='categories' ? `${d.view_mode||'horizontal'} • ${d.card_style||'chip'}` : 'بانر';

        return `
        <div class="section-item" data-key="${key}" data-type="${type}" id="${id}">
            <div class="section-item-header" onclick="toggleSection(${idx})">
                <i class="bi bi-grip-vertical drag-handle" onclick="event.stopPropagation()"></i>
                <span style="font-size:22px;flex-shrink:0">${def.icon}</span>
                <div class="section-info">
                    <span class="section-name-display" id="td-${idx}">${esc(d.title)}</span>
                    <span class="section-summary">${summary}</span>
                </div>
                <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation()">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input section-visible-toggle" type="checkbox"
                               id="vis-${idx}" checked onchange="toggleHidden('${id}',this.checked)">
                    </div>
                    <span class="order-badge" id="badge-${idx}">-</span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                            onclick="event.stopPropagation();removeSection('${id}')">
                        <i class="bi bi-trash"></i>
                    </button>
                    <i class="bi bi-chevron-down section-chevron" id="chev-${idx}"></i>
                </div>
            </div>
            <div class="section-settings-body collapse show" id="sb-${idx}">
                <div class="settings-panel">${panel}</div>
            </div>
        </div>`;
    }

    /* ── Card preview HTML helpers ── */
    function productPreview(pc, em) {
        if (pc==='pv-standard') return `<div class="img">${em}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>`;
        if (pc==='pv-minimal')  return `<div class="img">${em}</div><div class="info"><div class="pname">اسم المنتج</div><div class="pprice">15,000 د.ع</div><span class="pbtn">اطلب</span></div>`;
        if (pc==='pv-detailed') return `<div class="img">${em}</div><div class="info"><span class="pcat">قسم</span><div class="pname">اسم المنتج</div><div class="prow"><span>15,000</span><span>3,000</span></div><div class="pstock">✔ متوفر</div></div>`;
        if (pc==='pv-dark')     return `<div class="img">${em}</div><div class="info"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>`;
        if (pc==='pv-compact')  return `<span class="emoji">${em}</span><div class="poverlay"><div class="pname">اسم المنتج</div><span class="pbadge">ربح 5,000</span></div>`;
        return '';
    }
    function catPreview(pc) {
        const cats=['📱','👗','🍕','💄','🎮','🏠'];
        if (pc==='cv-chip')    return cats.map(e=>`<div class="citem"><div class="cbox">${e}</div><div class="clbl">قسم</div></div>`).join('');
        if (pc==='cv-gradient')return cats.map((e,i)=>`<div class="citem"><div class="cbox" style="background:${GC[i%GC.length]}">${e}</div><div class="clbl">قسم</div></div>`).join('');
        if (pc==='cv-card')    return ['📱','👗','🍕','🎮'].map(e=>`<div class="citem"><div class="cimg">${e}</div><div class="ctxt"><div class="cnm">قسم</div><div class="ccnt">12 منتج</div></div></div>`).join('');
        if (pc==='cv-list')    return [['📱','إلكترونيات'],['👗','ملابس'],['🍕','طعام'],['🎮','ألعاب']].map(([e,n])=>`<div class="citem"><span class="cicon">${e}</span><div class="cinfo"><div class="cnm">${n}</div><div class="ccnt">12 منتج</div></div><span class="carrow">›</span></div>`).join('');
        if (pc==='cv-circle')  return cats.map((e,i)=>`<div class="citem"><div class="cbox" style="background:linear-gradient(135deg,${GC[i%GC.length]},${GC[(i+2)%GC.length]})">${e}</div><div class="clbl">قسم</div></div>`).join('');
        return '';
    }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    /* ── Serialize before submit ── */
    document.getElementById('settingsForm').addEventListener('submit', function () {
        const sections = [];
        listEl.querySelectorAll('.section-item').forEach(item => {
            const key  = item.dataset.key;
            const def  = DEFS[key] || { type: 'products' };
            const type = item.dataset.type || def.type;
            const sec  = {
                key,
                title:   item.querySelector('.section-title-input')?.value || key,
                visible: item.querySelector('.section-visible-toggle')?.checked ?? true,
            };
            if (type === 'products') {
                sec.view_mode  = item.querySelector('.section-view-mode:checked')?.value  || 'grid';
                sec.card_style = item.querySelector('.section-card-style:checked')?.value || 'standard';
                sec.count      = parseInt(item.querySelector('.section-count')?.value) || 10;
            }
            if (key === 'categories') {
                sec.view_mode  = item.querySelector('.section-view-mode:checked')?.value  || 'horizontal';
                sec.card_style = item.querySelector('.section-card-style:checked')?.value || 'chip';
                sec.count      = parseInt(item.querySelector('.section-count')?.value) || 8;
            }
            if (type === 'category_products') {
                sec.type          = 'category_products';
                sec.category_id   = parseInt(item.querySelector('.section-category-id')?.value) || null;
                sec.category_name = item.querySelector('.section-category-name')?.textContent?.trim() || '';
                sec.view_mode     = item.querySelector('.section-view-mode:checked')?.value  || 'horizontal';
                sec.card_style    = item.querySelector('.section-card-style:checked')?.value || 'standard';
                sec.count         = parseInt(item.querySelector('.section-count')?.value) || 10;
            }
            if (key === 'banners') {
                sec.auto_play = item.querySelector('.section-auto-play')?.checked ?? true;
                sec.duration  = parseInt(item.querySelector('.section-duration')?.value) || 4;
            }
            sections.push(sec);
        });
        document.getElementById('sections_config_input').value = JSON.stringify(sections);
    });

    rebadge();

    /* ── Custom Category Section Creator ── */
    let _catsLoaded = false;
    function loadCategories() {
        if (_catsLoaded) return;
        const sel     = document.getElementById('cc-category');
        const loading = document.getElementById('cc-cat-loading');
        if (!sel) return;
        if (loading) loading.classList.remove('d-none');
        fetch('/admin/home-settings/categories-json', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(cats => {
                sel.innerHTML = '<option value="">—— اختر فئة ——</option>' +
                    cats.map(c => `<option value="${c.id}" data-name="${esc(c.name_ar)}">${c.icon||'🗂️'} ${esc(c.name_ar)}</option>`).join('');
                _catsLoaded = true;
                if (loading) loading.classList.add('d-none');
            })
            .catch(() => {
                if (loading) loading.classList.add('d-none');
                if (sel) sel.innerHTML = '<option value="">—— خطأ في التحميل ——</option>';
            });
    }

    window.toggleCustomCreator = function () {
        const body = document.getElementById('custom-creator-body');
        const chev = document.getElementById('creator-chev');
        if (!body) return;
        const open = body.style.display !== 'none';
        body.style.display = open ? 'none' : '';
        if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
    };

    window.addCustomCategorySection = function () {
        const titleEl = document.getElementById('cc-title');
        const catEl   = document.getElementById('cc-category');
        const vmEl    = document.querySelector('input[name="cc_vm"]:checked');
        const csEl    = document.querySelector('input[name="cc_cs"]:checked');
        const countEl = document.getElementById('cc-count');

        const title   = titleEl?.value?.trim();
        const catId   = catEl?.value;
        const catName = catEl?.options[catEl?.selectedIndex]?.dataset?.name
                     || catEl?.options[catEl?.selectedIndex]?.text?.replace(/^[^\s]+\s+/,'') || '';

        if (!title) {
            titleEl?.classList.add('is-invalid');
            titleEl?.focus();
            setTimeout(() => titleEl?.classList.remove('is-invalid'), 2500);
            return;
        }
        if (!catId) {
            catEl?.classList.add('is-invalid');
            setTimeout(() => catEl?.classList.remove('is-invalid'), 2500);
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('addSectionModal'))?.hide();

        const idx = idCtr++;
        const id  = 'si-' + idx;
        const key = 'cat_sec_' + idx;
        const d   = {
            title,
            type:          'category_products',
            category_id:   parseInt(catId),
            category_name: catName,
            view_mode:     vmEl?.value  || 'horizontal',
            card_style:    csEl?.value  || 'standard',
            count:         parseInt(countEl?.value) || 10,
        };
        const customDef = { icon: '🗂️', label: title, type: 'category_products' };
        listEl.insertAdjacentHTML('beforeend', buildItemHTML(key, customDef, idx, id, d));
        rebadge();
        refreshModal();
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Reset form
        if (titleEl)  titleEl.value  = '';
        if (catEl)    catEl.value    = '';
        if (countEl)  { countEl.value = 10; const dp = document.getElementById('cc-count-display'); if (dp) dp.textContent = '10'; }
        const hmEl = document.getElementById('cc_vm_horizontal');
        if (hmEl) hmEl.checked = true;
        const firstCs = document.querySelector('input[name="cc_cs"]');
        if (firstCs) firstCs.checked = true;
    };

    // Fill card style picker inside creator form
    (function initCreatorCardStyles() {
        const grid = document.getElementById('cc-card-style-grid');
        if (!grid) return;
        grid.innerHTML = psOpts.map(([v,lb,pc,em], i) => `
        <div class="card-style-option">
            <input type="radio" name="cc_cs" id="cc_cs_${v}" value="${v}" class="section-card-style" ${i===0?'checked':''}>
            <label for="cc_cs_${v}">
                <div class="card-style-preview ${pc}">${productPreview(pc,em)}</div>
                <div class="card-style-label">${lb}</div>
            </label>
        </div>`).join('');
    })();

})();
</script>
@endpush
