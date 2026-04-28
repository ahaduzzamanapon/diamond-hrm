<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'createdBy'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->supplier_id);
        
        $purchases = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->get();
        return view('inventory.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get(['id', 'name', 'sku']);
        return view('inventory.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'reference_no' => $request->reference_no,
                'note' => $request->note,
                'total_amount' => 0, // Will calculate
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $total += $subtotal;
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }
            $purchase->update(['total_amount' => $total]);
        });

        return redirect()->route('inventory.purchases.index')->with('success', 'Purchase recorded successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'createdBy']);
        return view('inventory.purchases.show', compact('purchase'));
    }

    public function receive(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'Purchase is already received.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $item->product->increment('current_stock', $item->quantity);
            }
            $purchase->update(['status' => 'received']);
        });

        return back()->with('success', 'Purchase marked as received. Stock updated.');
    }
}
