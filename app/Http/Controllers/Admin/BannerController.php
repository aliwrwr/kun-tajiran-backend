<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /** @var FirestoreService */
    private $fs;

    public function __construct(FirestoreService $fs)
    {
        $this->fs = $fs;
    }

    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('created_at')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image'       => 'required|image|max:3072',
            'title'       => 'nullable|string|max:100',
            'subtitle'    => 'nullable|string|max:150',
            'link_type'   => 'required|in:none,product,url',
            'link'        => 'nullable|string|max:255',
            'badge_text'  => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'image'       => $imagePath,
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'link_type'   => $request->link_type,
            'link'        => $request->link,
            'badge_text'  => $request->badge_text,
            'badge_color' => $request->badge_color ?? '#FF6B35',
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => true,
        ]);

        $this->syncBannerToFirestore($banner);

        return redirect()->route('admin.banners.index')
            ->with('success', 'تمت إضافة البانر بنجاح');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image'       => 'nullable|image|max:3072',
            'title'       => 'nullable|string|max:100',
            'subtitle'    => 'nullable|string|max:150',
            'link_type'   => 'required|in:none,product,url',
            'link'        => 'nullable|string|max:255',
            'badge_text'  => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data = [
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'link_type'   => $request->link_type,
            'link'        => $request->link,
            'badge_text'  => $request->badge_text,
            'badge_color' => $request->badge_color ?? '#FF6B35',
            'sort_order'  => $request->sort_order ?? 0,
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        $banner->refresh();
        $this->syncBannerToFirestore($banner);

        return redirect()->route('admin.banners.index')
            ->with('success', 'تم تحديث البانر بنجاح');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        $this->fs->update('banners', (string) $banner->id, [
            'is_active' => (bool) $banner->is_active,
        ]);
        return back()->with('success', 'تم تغيير حالة البانر');
    }

    public function destroy(Banner $banner)
    {
        $this->fs->delete('banners', (string) $banner->id);
        Storage::disk('public')->delete($banner->image);
        $banner->delete();
        return back()->with('success', 'تم حذف البانر');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function syncBannerToFirestore(Banner $banner): void
    {
        $imageUrl = $banner->image
            ? (str_starts_with($banner->image, 'http')
                ? $banner->image
                : asset('storage/' . $banner->image))
            : null;

        $this->fs->set('banners', (string) $banner->id, [
            'id'          => (string) $banner->id,
            'image'       => $imageUrl,
            'title'       => $banner->title,
            'subtitle'    => $banner->subtitle,
            'link_type'   => $banner->link_type,
            'link'        => $banner->link,
            'badge_text'  => $banner->badge_text,
            'badge_color' => $banner->badge_color,
            'sort_order'  => (int) ($banner->sort_order ?? 0),
            'is_active'   => (bool) $banner->is_active,
        ]);
    }
}
