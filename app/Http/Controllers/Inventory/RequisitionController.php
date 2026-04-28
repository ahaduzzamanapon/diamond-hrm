<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        $query = Requisition::with(['employee', 'department'])->latest();
        
        // Non-admins see only their own dept or own requests
        // Let's assume anyone can see all for now, but usually restricted
        if ($request->filled('status')) $query->where('status', $request->status);

        $requisitions = $query->paginate(20)->withQueryString();
        return view('inventory.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get(['id', 'name', 'current_stock']);
        // If employee logic exists, prepopulate or let select
        $employeeId = Auth::user()->employee?->id ?? null;
        $departmentId = Auth::user()->employee?->department_id ?? null;

        return view('inventory.requisitions.create', compact('products', 'employeeId', 'departmentId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $req = Requisition::create([
                'employee_id' => $request->employee_id ?? Auth::user()->employee?->id,
                'department_id' => $request->department_id ?? Auth::user()->employee?->department_id,
                'request_date' => $request->request_date,
                'note' => $request->note,
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                RequisitionItem::create([
                    'requisition_id' => $req->id,
                    'product_id' => $item['product_id'],
                    'qty_requested' => $item['quantity'],
                    'qty_supplied' => 0
                ]);
            }
        });

        return redirect()->route('inventory.requisitions.index')->with('success', 'Requisition created successfully.');
    }

    public function show(Requisition $requisition)
    {
        $requisition->load(['employee', 'department', 'items.product', 'approvedBy']);
        return view('inventory.requisitions.show', compact('requisition'));
    }

    public function approve(Requisition $requisition)
    {
        $requisition->update(['status' => 'approved', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Requisition approved.');
    }

    public function reject(Requisition $requisition)
    {
        $requisition->update(['status' => 'rejected']);
        return back()->with('success', 'Requisition rejected.');
    }

    public function supply(Request $request, Requisition $requisition)
    {
        if ($requisition->status === 'supplied') {
            return back()->with('error', 'Already supplied.');
        }

        DB::transaction(function () use ($request, $requisition) {
            foreach ($request->items as $itemId => $qtySupplied) {
                $reqItem = RequisitionItem::find($itemId);
                if ($reqItem && $reqItem->requisition_id === $requisition->id) {
                    $qty = (int)$qtySupplied;
                    if ($qty > 0) {
                        // Deduct from product stock
                        $reqItem->product->decrement('current_stock', $qty);
                        $reqItem->update(['qty_supplied' => $qty]);
                    }
                }
            }
            $requisition->update(['status' => 'supplied']);
        });

        return back()->with('success', 'Requisition marked as supplied and stock deducted.');
    }
}
