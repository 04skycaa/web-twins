<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Promo;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index()
    {
        $outlets = Outlet::where('status_aktif', true)->get();
        $now = Carbon::now();
        $promos = Promo::with(['products.category', 'products.stores'])
            ->where('status', true)
            ->where('tanggal_mulai', '<=', $now)
            ->where('tanggal_selesai', '>=', $now)
            ->get();

        $promoProducts = [];
        foreach ($promos as $promo) {
            foreach ($promo->products as $product) {
                $promoProducts[] = (object) [
                    'nama_promo' => $promo->nama_promo,
                    'tipe' => $promo->tipe,
                    'nilai' => $promo->nilai,
                    'product_name' => $product->nama_produk,
                    'image_url' => $this->resolveImageUrl($product->image_url),
                    'category' => $product->category ? $product->category->nama_category : 'Bahan Kue',
                    'price' => $product->harga_jual,
                    'outlet_name' => 'Tersedia di berbagai cabang',
                    'outlet_address' => 'Silakan pilih outlet terdekat'
                ];
            }
        }

        return view('welcome', compact('outlets', 'promoProducts'));
    }

    public function showOutlet($id)
    {
        $outlet = Outlet::findOrFail($id);
        $productStores = ProductStore::with(['product.category'])
            ->where('store_id', $outlet->uuid)
            ->where('status_aktif', true)
            ->get();

        $products = $productStores->map(function ($ps) {
            return [
                'id' => $ps->product->uuid,
                'name' => $ps->product->nama_produk,
                'price' => (int) $ps->product->harga_jual,
                'category' => $ps->product->category ? str_replace([' ', '&'], ['_', ''], strtolower($ps->product->category->nama_category)) : 'olahan',
                'img' => $this->resolveImageUrl($ps->product->image_url),
                'rating' => 4.8 
            ];
        });

        $categories = Category::all()->map(function($cat) {
            return [
                'id' => str_replace([' ', '&'], ['_', ''], strtolower($cat->nama_category)),
                'name' => $cat->nama_category
            ];
        });

        return view('user', compact('outlet', 'products', 'categories'));
    }

    private function resolveImageUrl($path)
    {
        if (!$path) {
            return asset('images/terigu.jpg');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (file_exists(public_path('images/' . $path))) {
            return asset('images/' . $path);
        }

        return asset('storage/' . $path);
    }
}
