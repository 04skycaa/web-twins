<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Promo;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Category;
use App\Models\StoreReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LandingController extends Controller
{
    public function index()
    {
        $outlets = Outlet::where('status_aktif', true)->get();
        $now = Carbon::now();
        $promos = Promo::with('stores')
            ->where('status', true)
            ->whereNotNull('image_banner')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $promoProducts = [];
        foreach ($promos as $promo) {
            $store = $promo->stores->first();

            $promoProducts[] = (object) [
                'nama_promo' => $promo->nama_promo,
                'image_banner' => self::resolveImageUrl($promo->image_banner),
                'deskripsi' => $promo->deskripsi,
                'outlet_name' => $store ? $store->nama : 'TWINS Bakery',
                'outlet_address' => $store ? $store->alamat : 'Semua Cabang'
            ];
        }

        $testimonials = StoreReview::with(['user', 'store'])
            ->where('rating', '>=', 4)
            ->orderBy('created_at', 'desc')
            ->limit(14)
            ->get();

        return view('welcome', compact('outlets', 'promoProducts', 'testimonials'));
    }

    public function showOutlet($id)
    {
        $outlet = Outlet::findOrFail($id);
        // 1. Ambil promo aktif untuk store ini
        $activePromos = Promo::where('status', true)
            ->whereHas('stores', function ($q) use ($outlet) {
                $q->where('store_id', $outlet->uuid);
            })
            ->with([
                'products' => function ($q) {
                    // Jangan hanya select uuid, tapi ambil semua data yang dibutuhkan View
                    $q->select('products.uuid', 'products.nama_produk', 'products.harga_jual', 'products.image_url', 'products.kategori_id');
                }
            ])
            ->get();

        // 2. Map produk ke diskon (Ambil diskon terbesar jika ada multiple)
        $productDiscounts = [];
        foreach ($activePromos as $promo) {
            foreach ($promo->products as $p) {
                $tipe = strtolower($p->pivot->tipe_diskon); // Paksa huruf kecil sesuai skema
                $nilai = (int) $p->pivot->nilai_diskon;

                $productDiscounts[$p->uuid] = [
                    'tipe' => $tipe,
                    'nilai' => $nilai,
                    'nama' => $promo->nama_promo
                ];
            }
        }

        $products = Product::with('priceLevels')
            ->join('product_store', 'products.uuid', '=', 'product_store.product_id')
            ->where('product_store.store_id', $outlet->uuid)
            ->select(
                'products.*',
                'product_store.stok as stok'
            )
            ->get()
            ->map(function ($p) use ($productDiscounts) {
                $originalPrice = (int) $p->harga_jual;
                $discountPrice = $originalPrice;
                $isDiscount = false;
                $discountLabel = '';

                if (isset($productDiscounts[$p->uuid])) {
                    $d = $productDiscounts[$p->uuid];
                    $isDiscount = true;
                    if ($d['tipe'] === 'persen') {
                        $discountPrice = $originalPrice - ($originalPrice * ($d['nilai'] / 100));
                        $discountLabel = $d['nilai'] . '%';
                    } else {
                        $discountPrice = $originalPrice - $d['nilai'];
                        $discountLabel = 'Rp ' . number_format($d['nilai'], 0, ',', '.');
                    }
                }

                return [
                    'id' => $p->uuid,
                    'name' => $p->nama_produk,
                    'price' => (int) $discountPrice,
                    'original_price' => $originalPrice,
                    'is_discount' => $isDiscount,
                    'discount_label' => $discountLabel,
                    'stok' => (int) $p->stok,
                    'category_id' => $p->kategori_id,
                    'category' => $p->kategori_id,
                    'img' => \App\Http\Controllers\LandingController::resolveImageUrl($p->image_url),
                    'price_levels' => $p->priceLevels->map(function ($level) {
                        return [
                            'uuid' => $level->uuid,
                            'product_id' => $level->product_id,
                            'jmlh' => (int) $level->jmlh,
                            'harga' => (int) $level->harga,
                        ];
                    })->values()->all(),
                    'rating' => 4.8
                ];
            })->values()->all();

        $categories = Category::all()->map(function ($cat) {
            return [
                'id' => $cat->uuid,
                'name' => $cat->nama_category
            ];
        });

        $reviews = StoreReview::with('user')
            ->where('store_id', $outlet->uuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $stockMap = [];
        foreach ($products as $p) {
            $stockMap[$p['id']] = $p['stok'];
        }

        $storedDeliveryAddress = session('delivery_address.' . $outlet->uuid);
        $deliveryPreference = null;

        if (is_array($storedDeliveryAddress) && !empty($storedDeliveryAddress['address'])) {
            $coords = $storedDeliveryAddress['coordinates'] ?? null;
            $validCoordinates = is_array($coords)
                && isset($coords['lat'], $coords['lng'])
                && is_numeric($coords['lat'])
                && is_numeric($coords['lng']);

            $deliveryPreference = [
                'address' => trim((string) $storedDeliveryAddress['address']),
                'coordinates' => $validCoordinates ? [
                    'lat' => (float) $coords['lat'],
                    'lng' => (float) $coords['lng'],
                ] : null,
            ];
        }

        // Ambil promo untuk banner (Sama seperti activePromos tapi dengan relasi lengkap)
        $discounts = $activePromos;

        return view('user', compact('outlet', 'products', 'categories', 'reviews', 'discounts', 'stockMap', 'deliveryPreference'));
    }

    public function saveDeliveryAddress(Request $request, $id)
    {
        $outlet = Outlet::findOrFail($id);

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:1000'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $coordinates = null;
        if (isset($validated['coordinates']['lat'], $validated['coordinates']['lng'])) {
            $coordinates = [
                'lat' => (float) $validated['coordinates']['lat'],
                'lng' => (float) $validated['coordinates']['lng'],
            ];
        }

        $deliveryData = [
            'address' => trim($validated['address']),
            'coordinates' => $coordinates,
            'updated_at' => now()->toIso8601String(),
        ];

        session()->put('delivery_address.' . $outlet->uuid, $deliveryData);

        return response()->json([
            'message' => 'Alamat pengiriman tersimpan aman di sesi server.',
            'delivery' => $deliveryData,
        ]);
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $outlet = Outlet::findOrFail($id);
        $exists = StoreReview::where('store_id', $outlet->uuid)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk toko ini.');
        }

        DB::transaction(function () use ($request, $outlet) {
            StoreReview::create([
                'store_id' => $outlet->uuid,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Update average rating in store table
            $avgRating = StoreReview::where('store_id', $outlet->uuid)->avg('rating');
            $outlet->update(['rating' => $avgRating]);
        });

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    public function generalReview(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:store,uuid',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $outlet = Outlet::findOrFail($request->store_id);

        $exists = StoreReview::where('store_id', $outlet->uuid)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk cabang ini.');
        }

        DB::transaction(function () use ($request, $outlet) {
            StoreReview::create([
                'store_id' => $outlet->uuid,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            $avgRating = StoreReview::where('store_id', $outlet->uuid)->avg('rating');
            $outlet->update(['rating' => $avgRating]);
        });

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    public static function resolveImageUrl($path)
    {
        if (!$path) {
            return asset('images/placeholder-product.png');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Clean path from /storage/ prefix for Storage::url()
        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
    }

    public static function uploadToCloudinary($file, $folder = 'twins')
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $uploadPreset = env('CLOUDINARY_UPLOAD_PRESET', 'ml_default');

        if (!$cloudName) {
            return null;
        }

        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        try {
            // Jika file adalah base64 string
            if (is_string($file) && str_starts_with($file, 'data:image')) {
                $response = Http::asForm()->post($url, [
                    'file' => $file,
                    'upload_preset' => $uploadPreset,
                    'folder' => $folder
                ]);
            }
            // Jika file adalah UploadedFile object
            else {
                $response = Http::attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                    ->post($url, [
                        'upload_preset' => $uploadPreset,
                        'folder' => $folder
                    ]);
            }

            if ($response->successful()) {
                return $response->json()['secure_url'];
            }

            \Illuminate\Support\Facades\Log::error('Cloudinary Upload Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cloudinary Exception: ' . $e->getMessage());
            return null;
        }
    }
}
