<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSetting;
use Illuminate\Http\Request;

class HomeSettingsController extends Controller
{
    public function index()
    {
        $defaults = HomeSetting::defaults();
        $saved    = HomeSetting::getAllAsArray();
        $settings = array_merge($defaults, $saved);
        return view('admin.home-settings.index', compact('settings'));
    }

    public function categoriesJson(): \Illuminate\Http\JsonResponse
    {
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name_ar', 'icon'])
            ->toArray();
        return response()->json($categories);
    }

    public function update(Request $request)
    {
        $request->validate([
            'sections_config' => 'required|string',
        ]);

        $decoded = json_decode($request->sections_config, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->withErrors(['sections_config' => 'بيانات الأقسام غير صالحة']);
        }

        HomeSetting::setValue('sections_config', $request->sections_config);

        return back()->with('success', 'تم حفظ إعدادات الشاشة الرئيسية بنجاح ✅');
    }
}
