<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::withCount('products')->get();
        return view('inventory.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        InventoryCategory::create($request->only('name', 'description'));
        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, InventoryCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category->update($request->only('name', 'description', 'is_active'));
        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(InventoryCategory $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated products.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted successfully.');
    }
}
