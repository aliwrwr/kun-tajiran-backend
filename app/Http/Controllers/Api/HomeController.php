<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSetting;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        // Load section configuration
        $saved          = HomeSetting::getAllAsArray();
        $defaultConfig  = HomeSetting::defaults()['sections_config'];
        $sectionsConfig = json_decode($saved['sections_config'] ?? $defaultConfig, true)
                          ?? json_decode($defaultConfig, true);
        $byKey = collect($sectionsConfig)->keyBy('key');

        // Load all active products once
        $allProducts = Product::with('category')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();

        // ── Banners ──────────────────────────────────────────────────────
        $banners   = [];
        $bannerCfg = $byKey->get('banners', []);
        if ($bannerCfg['visible'] ?? true) {
            $rawBanners = Banner::where('is_active', true)->orderBy('sort_order')->limit(20)->get();
            $banners    = $rawBanners->map(function ($b) {
                $image = $b->image;
                if ($image && !str_starts_with($image, 'http')) {
                    $image = asset('storage/' . $image);
                }
                return [
                    'id'          => (string) $b->id,
                    'title'       => $b->title,
                    'subtitle'    => $b->subtitle,
                    'image_url'   => $image,
                    'link'        => $b->link,
                    'link_type'   => $b->link_type ?? null,
                    'badge_text'  => $b->badge_text,
                    'badge_color' => $b->badge_color ?? null,
                ];
            })->values()->all();
        }

        // ── Featured products ─────────────────────────────────────────────
        $featured    = [];
        $featuredCfg = $byKey->get('featured', []);
        if ($featuredCfg['visible'] ?? true) {
            $count    = (int) ($featuredCfg['count'] ?? 6);
            $featured = $allProducts
                ->where('is_featured', true)
                ->take($count)
                ->map(function ($p) { return $this->formatProductModel($p); })
                ->values()->all();
        }

        // ── Categories ───────────────────────────────────────────────────
        $categories = [];
        $catCfg     = $byKey->get('categories', []);
        if ($catCfg['visible'] ?? true) {
            $count         = (int) ($catCfg['count'] ?? 8);
            $productCounts = $allProducts->countBy('category_id');
            $rawCats       = Category::where('is_active', true)->orderBy('sort_order')->limit($count)->get();
            $categories    = $rawCats->map(function ($c) use ($productCounts) {
                $image = $c->image;
                if ($image && !str_starts_with($image, 'http')) {
                    $image = asset('storage/' . $image);
                }
                return [
                    'id'        => (string) $c->id,
                    'name'      => $c->name_ar ?? $c->name ?? '',
                    'icon'      => $c->icon ?? null,
                    'image_url' => $image,
                    'count'     => (int) ($productCounts[$c->id] ?? 0),
                ];
            })->values()->all();
        }

        // ── Products section ──────────────────────────────────────────────
        $products    = [];
        $productsCfg = $byKey->get('products', []);
        if ($productsCfg['visible'] ?? true) {
            $count    = (int) ($productsCfg['count'] ?? 10);
            $products = $allProducts
                ->take($count)
                ->map(function ($p) { return $this->formatProductModel($p); })
                ->values()->all();
        }

        // ── Custom category-product sections ─────────────────────────────
        $sectionsData = new \stdClass();
        foreach ($sectionsConfig as $sec) {
            if (($sec['type'] ?? '') !== 'category_products') continue;
            if (!($sec['visible'] ?? true)) continue;
            $sKey  = $sec['key'] ?? '';
            $catId = (int) ($sec['category_id'] ?? 0);
            if (!$sKey || !$catId) continue;
            $count = (int) ($sec['count'] ?? 10);
            $subset = $allProducts->where('category_id', $catId)->take($count);
            $sectionsData->{$sKey} = $subset->map(function ($p) {
                return $this->formatProductModel($p);
            })->values()->all();
        }

        return response()->json([
            'settings'      => ['sections' => $sectionsConfig],
            'banners'       => $banners,
            'featured'      => $featured,
            'categories'    => $categories,
            'products'      => $products,
            'sections_data' => $sectionsData,
        ]);
    }

    private function formatProductModel(Product $product): array
    {
        $images = collect($product->images ?? [])->map(function ($img) {
            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
        })->values()->all();

        return [
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
    }
}

    {
        $this->fs = $fs;
    }

    public function index(): JsonResponse
    {
        // Load section configuration (from Firestore, fallback to MySQL HomeSetting)
        $settingsDoc    = $this->fs->get('settings', 'app_settings');
        $sectionsConfig = $settingsDoc['sections'] ?? null;

        if (!$sectionsConfig) {
            $saved          = HomeSetting::getAllAsArray();
            $defaultConfig  = HomeSetting::defaults()['sections_config'];
            $sectionsConfig = json_decode($saved['sections_config'] ?? $defaultConfig, true)
                              ?? json_decode($defaultConfig, true);
        }

        $byKey = collect($sectionsConfig)->keyBy('key');

        // ── Load all active products once (used for multiple sections) ──
        $allProducts = $this->fs->runQuery('products', [
            ['is_active', '=', true],
        ], null, 'ASCENDING', 300);
        usort($allProducts, fn($a, $b) => ($b['created_at'] ?? '') <=> ($a['created_at'] ?? ''));

        // ── Banners ──────────────────────────────────────────────────────
        $banners    = [];
        $bannerCfg  = $byKey->get('banners', []);
        if ($bannerCfg['visible'] ?? true) {
            $rawBanners = $this->fs->runQuery('banners', [
                ['is_active', '=', true],
            ], null, 'ASCENDING', 20);
            usort($rawBanners, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
            $banners = array_map(fn($b) => [
                'id'          => $b['id'],
                'title'       => $b['title'] ?? null,
                'subtitle'    => $b['subtitle'] ?? null,
                'image_url'   => $b['image'] ?? null,
                'link'        => $b['link'] ?? null,
                'link_type'   => $b['link_type'] ?? null,
                'badge_text'  => $b['badge_text'] ?? null,
                'badge_color' => $b['badge_color'] ?? null,
            ], $rawBanners);
        }

        // ── Featured products ─────────────────────────────────────────────
        $featured    = [];
        $featuredCfg = $byKey->get('featured', []);
        if ($featuredCfg['visible'] ?? true) {
            $count       = (int) ($featuredCfg['count'] ?? 6);
            $featuredRaw = array_filter($allProducts, fn($p) => (bool) ($p['is_featured'] ?? false));
            $featured    = array_map(fn($p) => $this->formatProduct($p), array_slice(array_values($featuredRaw), 0, $count));
        }

        // ── Categories ───────────────────────────────────────────────────
        $categories = [];
        $catCfg     = $byKey->get('categories', []);
        if ($catCfg['visible'] ?? true) {
            $count         = (int) ($catCfg['count'] ?? 8);
            $rawCats = $this->fs->runQuery('categories', [['is_active', '=', true]], null, 'ASCENDING', 50);
            usort($rawCats, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
            $rawCats = array_slice($rawCats, 0, $count);
            $productCounts = array_count_values(array_column($allProducts, 'category_id'));
            $categories    = array_map(fn($c) => [
                'id'        => $c['id'],
                'name'      => $c['name_ar'] ?? $c['name'] ?? '',
                'icon'      => $c['icon'] ?? null,
                'image_url' => $c['image'] ?? null,
                'count'     => (int) ($productCounts[$c['id']] ?? 0),
            ], $rawCats);
        }

        // ── Products ──────────────────────────────────────────────────────
        $products    = [];
        $productsCfg = $byKey->get('products', []);
        if ($productsCfg['visible'] ?? true) {
            $count    = (int) ($productsCfg['count'] ?? 10);
            $products = array_map(fn($p) => $this->formatProduct($p), array_slice($allProducts, 0, $count));
        }

        // ── Custom category-product sections ─────────────────────────────
        $sectionsData = new \stdClass(); // always encodes as JSON object, never array
        foreach ($sectionsConfig as $sec) {
            if (($sec['type'] ?? '') !== 'category_products') continue;
            if (!($sec['visible'] ?? true)) continue;
            $sKey  = $sec['key'] ?? '';
            $catId = (string) ($sec['category_id'] ?? '');
            if (!$sKey || !$catId) continue;
            $count  = (int) ($sec['count'] ?? 10);
            $subset = array_filter($allProducts, fn($p) => ($p['category_id'] ?? '') === $catId);
            $sectionsData->{$sKey} = array_map(fn($p) => $this->formatProduct($p), array_slice(array_values($subset), 0, $count));
        }

        return response()->json([
            'settings'      => ['sections' => $sectionsConfig],
            'banners'       => $banners,
            'featured'      => array_values($featured),
            'categories'    => $categories,
            'products'      => $products,
            'sections_data' => $sectionsData,
        ]);
    }

    private function formatProduct(array $p): array
    {
        $images = is_array($p['images'] ?? null) ? $p['images'] : [];

        return [
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
    }
}
