<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSetting;
use App\Models\Order;
use App\Models\Product;
use App\Services\FirestoreService;
use Illuminate\Console\Command;

/**
 * Migrate existing MySQL data to Firestore.
 *
 * Run once after configuring firebase-service-account.json:
 *   php artisan firestore:seed
 *
 * Seed a specific collection only:
 *   php artisan firestore:seed --collection=products
 */
class SeedFirestoreCommand extends Command
{
    protected $signature = 'firestore:seed
                            {--collection= : Seed only this collection (categories|products|banners|orders|settings)}
                            {--chunk=50    : Number of records to process per batch}';

    protected $description = 'Migrate MySQL data → Firestore (write-through initial seed)';

    public function __construct(private FirestoreService $fs)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $only = $this->option('collection');

        $this->info('يلا نبيع YallaSell — Firestore Seed');
        $this->info(str_repeat('─', 50));

        if (!$only || $only === 'categories') {
            $this->seedCategories();
        }

        if (!$only || $only === 'products') {
            $this->seedProducts();
        }

        if (!$only || $only === 'banners') {
            $this->seedBanners();
        }

        if (!$only || $only === 'orders') {
            $this->seedOrders();
        }

        if (!$only || $only === 'settings') {
            $this->seedSettings();
        }

        $this->info('');
        $this->info('✓ Firestore seeding complete!');

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Collections
    // ─────────────────────────────────────────────────────────────────────

    private function seedCategories(): void
    {
        $this->info('Seeding categories...');
        $categories = Category::all();

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $cat) {
            $imageUrl = $cat->image
                ? (str_starts_with($cat->image, 'http') ? $cat->image : asset('storage/' . $cat->image))
                : null;

            $this->fs->set('categories', (string) $cat->id, [
                'id'             => (string) $cat->id,
                'name_ar'        => $cat->name_ar,
                'name'           => $cat->name ?? $cat->name_ar,
                'icon'           => $cat->icon,
                'image'          => $imageUrl,
                'is_active'      => (bool) $cat->is_active,
                'sort_order'     => (int) ($cat->sort_order ?? 0),
                'products_count' => (int) $cat->products()->count(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  → {$categories->count()} categories synced.");
    }

    private function seedProducts(): void
    {
        $this->info('Seeding products...');
        $total = Product::count();
        $chunk = max(1, (int) $this->option('chunk'));

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Product::with('category')->chunkById($chunk, function ($products) use ($bar) {
            foreach ($products as $product) {
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
                    'updated_at'      => $product->updated_at?->toDateTimeString(),
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->line("  → {$total} products synced.");
    }

    private function seedBanners(): void
    {
        $this->info('Seeding banners...');
        $banners = Banner::all();

        foreach ($banners as $banner) {
            $imageUrl = $banner->image
                ? (str_starts_with($banner->image, 'http') ? $banner->image : asset('storage/' . $banner->image))
                : null;

            $this->fs->set('banners', (string) $banner->id, [
                'id'          => (string) $banner->id,
                'title'       => $banner->title,
                'subtitle'    => $banner->subtitle,
                'image'       => $imageUrl,
                'link'        => $banner->link,
                'link_type'   => $banner->link_type ?? 'none',
                'badge_text'  => $banner->badge_text,
                'badge_color' => $banner->badge_color ?? '#FF5252',
                'is_active'   => (bool) $banner->is_active,
                'sort_order'  => (int) ($banner->sort_order ?? 0),
            ]);
        }

        $this->line("  → {$banners->count()} banners synced.");
    }

    private function seedOrders(): void
    {
        $this->info('Seeding orders...');
        $total = Order::count();
        $chunk = max(1, (int) $this->option('chunk'));

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Order::with('items')->chunkById($chunk, function ($orders) use ($bar) {
            foreach ($orders as $order) {
                $items = $order->items->map(fn($i) => [
                    'product_id'      => (string) $i->product_id,
                    'product_name'    => $i->product_name,
                    'quantity'        => (int) $i->quantity,
                    'sale_price'      => (int) $i->sale_price,
                    'wholesale_price' => (int) $i->wholesale_price,
                    'profit_per_item' => (int) $i->profit_per_item,
                ])->values()->all();

                $this->fs->set('orders', (string) $order->id, [
                    'id'                    => (string) $order->id,
                    'order_number'          => $order->order_number,
                    'reseller_id'           => (string) $order->reseller_id,
                    'customer_name'         => $order->customer_name,
                    'customer_phone'        => $order->customer_phone,
                    'customer_city'         => $order->customer_city,
                    'customer_address'      => $order->customer_address,
                    'status'                => $order->status,
                    'payment_method'        => $order->payment_method ?? 'cod',
                    'total_sale_price'      => (int) $order->total_sale_price,
                    'total_wholesale_price' => (int) $order->total_wholesale_price,
                    'delivery_fee'          => (int) $order->delivery_fee,
                    'zone_delivery_fee'     => (int) ($order->zone_delivery_fee ?? 0),
                    'reseller_profit'       => (int) $order->reseller_profit,
                    'platform_profit'       => (int) ($order->platform_profit ?? 0),
                    'notes'                 => $order->notes,
                    'rejection_reason'      => $order->rejection_reason,
                    'delivered_at'          => $order->delivered_at?->toDateTimeString(),
                    'created_at'            => $order->created_at?->toDateTimeString(),
                    'updated_at'            => $order->updated_at?->toDateTimeString(),
                    'items'                 => $items,
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->line("  → {$total} orders synced.");
    }

    private function seedSettings(): void
    {
        $this->info('Seeding app settings...');

        $saved          = HomeSetting::getAllAsArray();
        $defaultConfig  = HomeSetting::defaults()['sections_config'];
        $sectionsConfig = json_decode($saved['sections_config'] ?? $defaultConfig, true)
                          ?? json_decode($defaultConfig, true);

        $this->fs->set('settings', 'app_settings', [
            'sections' => $sectionsConfig,
        ]);

        $this->line('  → App settings synced.');
    }
}
