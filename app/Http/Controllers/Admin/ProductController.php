<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(private FirestoreService $fs) {}
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name_ar', 'like', "%{$term}%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $perPage    = in_array((int) $request->per_page, [5, 10, 50, 100, 500]) ? (int) $request->per_page : 20;
        $products   = $query->paginate($perPage)->withQueryString();
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar'         => 'required|string|max:200',
            'name'            => 'required|string|max:200',
            'description_ar'  => 'nullable|string',
            'category_id'     => 'required|exists:categories,id',
            'wholesale_price' => 'required|integer|min:0',
            'suggested_price' => 'required|integer|min:0|gt:wholesale_price',
            'min_price'       => 'required|integer|min:0|lte:suggested_price',
            'stock_quantity'  => 'required|integer|min:0',
            'images'          => 'required|array|min:1',
            'images.*'        => 'image|mimes:jpeg,png,jpg,webp|max:3072',
            'youtube_url'     => 'nullable|url|max:500',
            'is_featured'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle image uploads
        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $imagePaths[] = $path;
        }

        $product = Product::create([
            'name_ar'         => $request->name_ar,
            'name'            => $request->name,
            'description_ar'  => $request->description_ar,
            'description'     => $request->description,
            'category_id'     => $request->category_id,
            'wholesale_price' => $request->wholesale_price,
            'suggested_price' => $request->suggested_price,
            'min_price'       => $request->min_price,
            'stock_quantity'  => $request->stock_quantity,
            'weight'          => $request->weight,
            'is_featured'     => $request->boolean('is_featured'),
            'is_active'       => true,
            'images'          => $imagePaths,
            'youtube_url'     => $request->youtube_url,
            'sku'             => $request->sku ?? uniqid('KT-'),
        ]);

        $this->syncProductToFirestore($product->load('category'));

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name_ar'         => 'required|string|max:200',
            'category_id'     => 'required|exists:categories,id',
            'wholesale_price' => 'required|integer|min:0',
            'suggested_price' => 'required|integer|min:0',
            'min_price'       => 'required|integer|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'new_images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:3072',
            'youtube_url'     => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $images = $product->images ?? [];

        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $images[] = $image->store('products', 'public');
            }
        }

        $product->update([
            'name_ar'         => $request->name_ar,
            'name'            => $request->name ?? $request->name_ar,
            'description_ar'  => $request->description_ar,
            'category_id'     => $request->category_id,
            'wholesale_price' => $request->wholesale_price,
            'suggested_price' => $request->suggested_price,
            'min_price'       => $request->min_price,
            'stock_quantity'  => $request->stock_quantity,
            'is_featured'     => $request->boolean('is_featured'),
            'is_active'       => $request->boolean('is_active'),
            'images'          => $images,
            'youtube_url'     => $request->youtube_url,
        ]);

        $this->syncProductToFirestore($product->load('category'));

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $this->fs->delete('products', (string) $product->id);
        $product->delete();
        return back()->with('success', 'تم حذف المنتج');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate(['quantity' => 'required|integer']);
        $product->increment('stock_quantity', $request->quantity);
        $this->fs->update('products', (string) $product->id, [
            'stock_quantity' => (int) $product->fresh()->stock_quantity,
        ]);
        return back()->with('success', 'تم تحديث المخزون');
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Sync a product record to Firestore (write-through).
     * Images are stored in local storage; full public URLs are saved to Firestore.
     */
    private function syncProductToFirestore(Product $product): void
    {
        $images = collect($product->images ?? [])->map(fn($img) =>
            str_starts_with($img, 'http') ? $img : asset('storage/' . $img)
        )->values()->all();

        $this->fs->set('products', (string) $product->id, [
            'id'              => (string) $product->id,
            'name_ar'         => $product->name_ar,
            'name'            => $product->name ?? $product->name_ar,
            'description_ar'  => $product->description_ar,
            'description'     => $product->description,
            'category_id'     => (string) $product->category_id,
            'category_name'   => $product->category?->name_ar,
            'wholesale_price' => (int) $product->wholesale_price,
            'suggested_price' => (int) $product->suggested_price,
            'min_price'       => (int) $product->min_price,
            'delivery_fee'    => (int) ($product->delivery_fee ?? 0),
            'stock_quantity'  => (int) $product->stock_quantity,
            'sales_count'     => (int) ($product->sales_count ?? 0),
            'is_active'       => (bool) $product->is_active,
            'is_featured'     => (bool) $product->is_featured,
            'images'          => $images,
            'youtube_url'     => $product->youtube_url,
            'sku'             => $product->sku,
            'weight'          => $product->weight,
            'created_at'      => $product->created_at?->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);
    }
}
