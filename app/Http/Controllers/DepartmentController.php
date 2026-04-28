<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Branch;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('branch')->withCount('employees', 'designations')->paginate(30);
        $branches    = Branch::where('is_active', true)->get();
        return view('departments.index', compact('departments', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'branch_id' => 'required|exists:branches,id']);
        Department::create($request->only('name', 'code', 'description', 'branch_id', 'is_active'));
        return back()->with('success', 'Department created!');
    }

    public function update(Request $request, Department $department)
    {
        $department->update($request->only('name', 'code', 'description', 'branch_id', 'is_active'));
        return back()->with('success', 'Department updated!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}
