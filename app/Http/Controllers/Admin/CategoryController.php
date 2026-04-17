<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\FirestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct(private FirestoreService $fs) {}
    public function index(): View
    {
        $perPage    = in_array((int) request('per_page'), [5, 10, 50, 100, 500]) ? (int) request('per_page') : 20;
        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->paginate($perPage)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100|unique:categories,name',
            'name_ar'    => 'required|string|max:100',
            'icon'       => 'nullable|string|max:10',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'name_ar', 'icon', 'sort_order']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? (Category::max('sort_order') + 1);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        // Sync new category to Firestore
        $this->syncCategoryToFirestore(Category::orderBy('id', 'desc')->first());

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إضافة القسم بنجاح');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function show(Category $category): View
    {
        $category->loadCount('products');
        $products = $category->products()->latest()->take(10)->get();
        return view('admin.categories.show', compact('category', 'products'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100|unique:categories,name,' . $category->id,
            'name_ar'    => 'required|string|max:100',
            'icon'       => 'nullable|string|max:10',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'name_ar', 'icon', 'sort_order']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        $this->syncCategoryToFirestore($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تحديث القسم بنجاح');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'لا يمكن حذف قسم يحتوي على منتجات');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $this->fs->delete('categories', (string) $category->id);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف القسم');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);
        $this->syncCategoryToFirestore($category);
        $label = $category->is_active ? 'تفعيل' : 'تعطيل';
        return redirect()->route('admin.categories.index')
            ->with('success', "تم {$label} القسم");
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function syncCategoryToFirestore(Category $category): void
    {
        $imageUrl = $category->image
            ? (str_starts_with($category->image, 'http') ? $category->image : asset('storage/' . $category->image))
            : null;

        $this->fs->set('categories', (string) $category->id, [
            'id'             => (string) $category->id,
            'name_ar'        => $category->name_ar,
            'name'           => $category->name ?? $category->name_ar,
            'icon'           => $category->icon,
            'image'          => $imageUrl,
            'is_active'      => (bool) $category->is_active,
            'sort_order'     => (int) ($category->sort_order ?? 0),
            'products_count' => (int) $category->products()->count(),
        ]);
    }
}
