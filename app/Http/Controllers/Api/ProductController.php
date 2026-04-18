<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderByDesc('sales_count')
            ->orderByDesc('created_at');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%");
            });
        }

        $products = $query->paginate(20);

        return response()->json([
            'products' => $products->getCollection()->map(function ($p) {
                return $this->formatProductModel($p);
            })->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('sales_count')
            ->limit(10)
            ->get();

        return response()->json([
            'featured' => $products->map(function ($p) {
                return $this->formatProductModel($p);
            })->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')
            ->where('is_active', true)
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'المنتج غير موجود'], 404);
        }

        return response()->json([
            'product' => $this->formatProductModel($product, true),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories->map(function ($c) {
                $image = $c->image;
                if ($image && !str_starts_with($image, 'http')) {
                    $image = asset('storage/' . $image);
                }
                return [
                    'id'        => (string) $c->id,
                    'name'      => $c->name_ar ?? $c->name ?? '',
                    'name_ar'   => $c->name_ar ?? '',
                    'icon'      => $c->icon ?? null,
                    'image_url' => $image,
                    'count'     => (int) $c->products_count,
                ];
            })->values(),
        ]);
    }

    private function formatProductModel(Product $product, bool $detailed = false): array
    {
        $images = collect($product->images ?? [])->map(function ($img) {
            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
        })->values()->all();

        $data = [
            'id'              => (string) $product->id,
            'name'            => $product->name_ar ?? $product->name ?? '',
            'thumbnail'       => $images[0] ?? null,
            'suggested_price' => (int) $product->suggested_price,
            'reseller_profit' => (int) ($product->suggested_price - $product->wholesale_price),
            'delivery_fee'    => (int) ($product->delivery_fee ?? 0),
            'stock'           => (int) $product->stock_quantity,
            'is_featured'     => (bool) $product->is_featured,
            'category'        => $product->category ? $product->category->name_ar : null,
        ];

        if ($detailed) {
            $data['images']      = $images;
            $data['youtube_url'] = $product->youtube_url;
            $data['description'] = $product->description_ar;
            $data['min_price']   = (int) $product->min_price;
            $data['weight']      = $product->weight;
            $data['category_id'] = (string) $product->category_id;
        }

        return $data;
    }
}
