<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $opnames = StockOpname::all();
        $requests = StockRequest::all(); 
        
        return view('product', compact('products', 'opnames', 'requests'));
    }
}