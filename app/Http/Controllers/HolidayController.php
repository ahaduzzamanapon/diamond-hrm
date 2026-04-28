<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year     = $request->year ?? now()->year;
        $holidays = Holiday::whereYear('date', $year)->with('branch')->orderBy('date')->get();
        $branches = \App\Models\Branch::all();
        return view('holidays.index', compact('holidays', 'year', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'date' => 'required|date']);
        Holiday::create($request->only('name', 'date', 'type', 'description', 'branch_id'));
        return back()->with('success', 'Holiday added!');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Holiday removed.');
    }
}
