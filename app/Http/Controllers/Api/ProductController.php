<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private FirestoreService $fs) {}

    /**
     * List all active products (with optional category filter & search) — reads from Firestore
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->fs->runQuery('products', [
            ['is_active', '=', true],
        ], null, 'ASCENDING', 300);

        // Sort: featured first, then by sales_count descending
        usort($products, function ($a, $b) {
            return ($b['sales_count'] ?? 0) <=> ($a['sales_count'] ?? 0);
        });

        if ($request->filled('category_id')) {
            $catId    = (string) $request->category_id;
            $products = array_values(array_filter($products, fn($p) => ($p['category_id'] ?? '') === $catId));
        }

        if ($request->filled('search')) {
            $term     = mb_strtolower($request->search);
            $products = array_values(array_filter($products, fn($p) =>
                str_contains(mb_strtolower($p['name_ar'] ?? ''), $term) ||
                str_contains(mb_strtolower($p['name'] ?? ''), $term)
            ));
        }

        // Featured first, then by sales_count
        usort($products, function ($a, $b) {
            $featuredDiff = (int)($b['is_featured'] ?? false) <=> (int)($a['is_featured'] ?? false);
            return $featuredDiff !== 0 ? $featuredDiff : (($b['sales_count'] ?? 0) <=> ($a['sales_count'] ?? 0));
        });

        $perPage = 20;
        $page    = max(1, (int) $request->get('page', 1));
        $total   = count($products);
        $paged   = array_slice($products, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'products' => array_map(fn($p) => $this->formatProduct($p), $paged),
            'meta' => [
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
                'total'        => $total,
            ],
        ]);
    }

    /**
     * Featured products for home screen slider — reads from Firestore
     */
    public function featured(): JsonResponse
    {
        $products = $this->fs->runQuery('products', [
            ['is_active', '=', true],
        ], null, 'ASCENDING', 100);
        $products = array_values(array_filter($products, fn($p) => (bool)($p['is_featured'] ?? false)));
        $products = array_slice($products, 0, 10);

        return response()->json([
            'featured' => array_map(fn($p) => $this->formatProduct($p), $products),
        ]);
    }

    /**
     * Get product details — reads from Firestore
     */
    public function show(int $id): JsonResponse
    {
        $p = $this->fs->get('products', (string) $id);

        if (!$p || !($p['is_active'] ?? false)) {
            return response()->json(['message' => 'المنتج غير موجود'], 404);
        }

        return response()->json([
            'product' => $this->formatProduct($p, detailed: true),
        ]);
    }

    /**
     * Get all active categories — reads from Firestore
     */
    public function categories(): JsonResponse
    {
        $categories = $this->fs->runQuery('categories', [
            ['is_active', '=', true],
        ], null, 'ASCENDING', 100);
        usort($categories, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return response()->json([
            'categories' => array_map(fn($c) => [
                'id'    => $c['id'],
                'name'  => $c['name_ar'] ?? $c['name'] ?? '',
                'icon'  => $c['icon'] ?? null,
                'image' => $c['image'] ?? null,
                'count' => (int) ($c['products_count'] ?? 0),
            ], $categories),
        ]);
    }

    // --- Private helpers ---
    private function formatProduct(array $p, bool $detailed = false): array
    {
        $images = is_array($p['images'] ?? null) ? $p['images'] : [];
        $data = [
            'id'              => $p['id'],
            'name'            => $p['name_ar'] ?? $p['name'] ?? '',
            'thumbnail'       => $images[0] ?? null,
            'suggested_price' => (int) ($p['suggested_price'] ?? 0),
            'reseller_profit' => (int) (($p['suggested_price'] ?? 0) - ($p['wholesale_price'] ?? 0)),
            'delivery_fee'    => (int) ($p['delivery_fee'] ?? 0),
            'stock'           => (int) ($p['stock_quantity'] ?? 0),
            'is_featured'     => (bool) ($p['is_featured'] ?? false),
            'category'        => $p['category_name'] ?? null,
        ];

        if ($detailed) {
            $data['images']      = $images;
            $data['youtube_url'] = $p['youtube_url'] ?? null;
            $data['description'] = $p['description_ar'] ?? null;
            $data['min_price']   = (int) ($p['min_price'] ?? 0);
            $data['weight']      = $p['weight'] ?? null;
            $data['category_id'] = $p['category_id'] ?? null;
        }

        return $data;
    }
}
