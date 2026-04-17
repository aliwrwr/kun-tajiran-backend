<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOffer;
use App\Models\DeliveryZone;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    // ─── Seed Iraqi provinces ─────────────────────────────────────────────────

    public function seedProvinces()
    {
        $provinces = [
            ['province_name' => 'بغداد',        'base_fee' => 5000],
            ['province_name' => 'البصرة',        'base_fee' => 7000],
            ['province_name' => 'نينوى',         'base_fee' => 7000],
            ['province_name' => 'أربيل',         'base_fee' => 8000],
            ['province_name' => 'السليمانية',    'base_fee' => 8000],
            ['province_name' => 'دهوك',          'base_fee' => 9000],
            ['province_name' => 'كركوك',         'base_fee' => 7000],
            ['province_name' => 'الأنبار',       'base_fee' => 8000],
            ['province_name' => 'بابل',          'base_fee' => 6000],
            ['province_name' => 'كربلاء',        'base_fee' => 6000],
            ['province_name' => 'النجف',         'base_fee' => 6000],
            ['province_name' => 'ميسان',         'base_fee' => 7000],
            ['province_name' => 'ذي قار',        'base_fee' => 7000],
            ['province_name' => 'المثنى',        'base_fee' => 8000],
            ['province_name' => 'القادسية',      'base_fee' => 7000],
            ['province_name' => 'واسط',          'base_fee' => 7000],
            ['province_name' => 'ديالى',         'base_fee' => 7000],
            ['province_name' => 'صلاح الدين',   'base_fee' => 7000],
        ];

        foreach ($provinces as $p) {
            DeliveryZone::firstOrCreate(
                ['province_name' => $p['province_name']],
                ['base_fee' => $p['base_fee'], 'is_active' => true]
            );
        }

        return back()->with('success', 'تم إضافة المحافظات العراقية بنجاح');
    }

    // ─── Zones ────────────────────────────────────────────────────────────────

    public function index()
    {
        $zones  = DeliveryZone::orderBy('province_name')->get();
        $offers = DeliveryOffer::with('sellers')->latest()->get();
        $sellers = User::where('role', 'reseller')->orderBy('name')->get();

        return view('admin.delivery-zones.index', compact('zones', 'offers', 'sellers'));
    }

    public function storeZone(Request $request)
    {
        $request->validate([
            'province_name' => 'required|string|max:100|unique:delivery_zones,province_name',
            'base_fee'      => 'required|integer|min:0',
        ]);

        DeliveryZone::create([
            'province_name' => $request->province_name,
            'base_fee'      => $request->base_fee,
            'is_active'     => true,
        ]);

        return back()->with('success', 'تم إضافة المحافظة بنجاح');
    }

    public function updateZone(Request $request, DeliveryZone $zone)
    {
        $request->validate([
            'province_name' => 'required|string|max:100|unique:delivery_zones,province_name,' . $zone->id,
            'base_fee'      => 'required|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);

        $zone->update([
            'province_name' => $request->province_name,
            'base_fee'      => $request->base_fee,
            'is_active'     => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث المحافظة');
    }

    public function destroyZone(DeliveryZone $zone)
    {
        $zone->delete();
        return back()->with('success', 'تم حذف المحافظة');
    }

    public function bulkUpdateZones(Request $request)
    {
        $request->validate([
            'zones'          => 'required|array',
            'zones.*.id'     => 'required|exists:delivery_zones,id',
            'zones.*.fee'    => 'required|integer|min:0',
            'zones.*.active' => 'nullable|boolean',
        ]);

        foreach ($request->zones as $zoneData) {
            DeliveryZone::where('id', $zoneData['id'])->update([
                'base_fee'  => $zoneData['fee'],
                'is_active' => isset($zoneData['active']) ? (bool) $zoneData['active'] : true,
            ]);
        }

        return back()->with('success', 'تم تحديث أجور التوصيل');
    }

    // ─── Offers ───────────────────────────────────────────────────────────────

    public function storeOffer(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'discount_type' => 'required|in:fixed,percentage,free',
            'discount_value'=> 'required_unless:discount_type,free|integer|min:0',
            'applies_to'    => 'required|in:all,specific_sellers',
            'seller_ids'    => 'nullable|array',
            'seller_ids.*'  => 'exists:users,id',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'is_active'     => 'nullable|boolean',
        ]);

        $offer = DeliveryOffer::create([
            'name'           => $request->name,
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_type === 'free' ? 0 : $request->discount_value,
            'applies_to'     => $request->applies_to,
            'starts_at'      => $request->starts_at,
            'ends_at'        => $request->ends_at,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        if ($request->applies_to === 'specific_sellers' && $request->filled('seller_ids')) {
            $offer->sellers()->sync($request->seller_ids);
        }

        return back()->with('success', 'تم إنشاء العرض بنجاح');
    }

    public function updateOffer(Request $request, DeliveryOffer $offer)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'discount_type' => 'required|in:fixed,percentage,free',
            'discount_value'=> 'required_unless:discount_type,free|integer|min:0',
            'applies_to'    => 'required|in:all,specific_sellers',
            'seller_ids'    => 'nullable|array',
            'seller_ids.*'  => 'exists:users,id',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
            'is_active'     => 'nullable|boolean',
        ]);

        $offer->update([
            'name'           => $request->name,
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_type === 'free' ? 0 : $request->discount_value,
            'applies_to'     => $request->applies_to,
            'starts_at'      => $request->starts_at,
            'ends_at'        => $request->ends_at,
            'is_active'      => $request->boolean('is_active'),
        ]);

        if ($request->applies_to === 'specific_sellers') {
            $offer->sellers()->sync($request->seller_ids ?? []);
        } else {
            $offer->sellers()->detach();
        }

        return back()->with('success', 'تم تحديث العرض');
    }

    public function destroyOffer(DeliveryOffer $offer)
    {
        $offer->delete();
        return back()->with('success', 'تم حذف العرض');
    }

    public function toggleOffer(DeliveryOffer $offer)
    {
        $offer->update(['is_active' => !$offer->is_active]);
        return back()->with('success', $offer->is_active ? 'تم تفعيل العرض' : 'تم تعطيل العرض');
    }

    // ─── API for Flutter ──────────────────────────────────────────────────────

    public function zonesJson()
    {
        $zones = DeliveryZone::active()->orderBy('province_name')->get(['id', 'province_name', 'base_fee']);
        return response()->json(['zones' => $zones]);
    }
}
