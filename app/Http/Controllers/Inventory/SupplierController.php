<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->paginate(20);
        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Supplier::create($request->all());
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchases');
        return view('inventory.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $supplier->update($request->all());
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with linked purchases.');
        }
        $supplier->delete();
        return redirect()->route('inventory.suppliers.index')->with('success', 'Supplier deleted.');
    }
}
