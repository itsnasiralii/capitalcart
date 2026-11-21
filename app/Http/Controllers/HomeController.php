<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Product::active()->featured()->inStock()->with(['images', 'category', 'reviews'])->limit(8)->get();
        if ($featured->isEmpty()) {
            $featured = Product::active()->inStock()->with(['images', 'category', 'reviews'])->limit(8)->get();
        }

        $categories = Category::active()->withCount('products')->orderByDesc('products_count')->limit(8)->get();
        $newArrivals = Product::active()->inStock()->with(['images', 'category'])->latest()->limit(8)->get();

        return view('pages.home', compact('featured', 'categories', 'newArrivals'));
    }
}
