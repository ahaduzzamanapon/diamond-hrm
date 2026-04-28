<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        if ($request->filled('category_id')) $query->where('inventory_category_id', $request->category_id);
        if ($request->filled('search')) $query->where('name', 'like', "%{$request->search}%")->orWhere('sku', 'like', "%{$request->search}%");
        if ($request->filled('stock_alert')) $query->whereColumn('current_stock', '<=', 'alert_quantity');

        $products = $query->paginate(20)->withQueryString();
        $categories = InventoryCategory::where('is_active', true)->get();

        return view('inventory.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = InventoryCategory::where('is_active', true)->get();
        $units = InventoryUnit::where('is_active', true)->get();
        return view('inventory.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'inventory_unit_id' => 'required|exists:inventory_units,id',
            'current_stock' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);
        return redirect()->route('inventory.products.index')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product)
    {
        $categories = InventoryCategory::where('is_active', true)->get();
        $units = InventoryUnit::where('is_active', true)->get();
        return view('inventory.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'inventory_unit_id' => 'required|exists:inventory_units,id',
        ]);

        $data = $request->all();
        
        // Handle checkbox
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('inventory.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return redirect()->route('inventory.products.index')->with('success', 'Product deleted.');
    }
}
