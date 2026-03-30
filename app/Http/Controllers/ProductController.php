<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockRequest; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'owner') {
            $products = Product::all();
        } else {
            // Pastikan kolom outlet_id ada di tabel products dan users
            $products = Product::where('outlet_id', $user->outlet_id)->get();
        }

        return view('products.index', compact('products'));
    }

    public function storeRequest(Request $request)
    {
        $user = Auth::user();

        StockRequest::create([
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity,
            'outlet_id'  => $user->outlet_id, 
            'status'     => 'pending'
        ]);
        
        return back()->with('success', 'Permintaan stok berhasil dikirim ke Owner.');
    }
}