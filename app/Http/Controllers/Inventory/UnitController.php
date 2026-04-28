<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = InventoryUnit::withCount('products')->get();
        return view('inventory.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        InventoryUnit::create($request->only('name', 'short_name'));
        return back()->with('success', 'Unit added successfully.');
    }

    public function update(Request $request, InventoryUnit $unit)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $unit->update($request->only('name', 'short_name', 'is_active'));
        return back()->with('success', 'Unit updated successfully.');
    }

    public function destroy(InventoryUnit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Cannot delete unit with associated products.');
        }
        $unit->delete();
        return back()->with('success', 'Unit deleted successfully.');
    }
}
