<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Department;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('department.branch')->withCount('employees')->paginate(30);
        $departments  = Department::where('is_active', true)->get();
        return view('designations.index', compact('designations', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'department_id' => 'required|exists:departments,id']);
        Designation::create($request->only('name', 'grade', 'description', 'department_id', 'is_active'));
        return back()->with('success', 'Designation created!');
    }

    public function update(Request $request, Designation $designation)
    {
        $designation->update($request->only('name', 'grade', 'description', 'department_id', 'is_active'));
        return back()->with('success', 'Designation updated!');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();
        return back()->with('success', 'Designation deleted.');
    }
}
