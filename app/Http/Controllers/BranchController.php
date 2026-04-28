<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('employees','departments')->paginate(20);
        return view('branches.index', compact('branches'));
    }

    public function create() { return view('branches.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'code' => 'required|unique:branches,code']);
        $data = $request->except(['_token', 'logo']);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('branch-logos', 'public');
        }
        Branch::create($data);
        return redirect()->route('branches.index')->with('success', 'Branch created!');
    }

    public function show(Branch $branch) { return view('branches.show', compact('branch')); }

    public function edit(Branch $branch) { return view('branches.edit', compact('branch')); }

    public function update(Request $request, Branch $branch)
    {
        $request->validate(['name' => 'required']);
        $data = $request->except(['_token', '_method', 'logo']);
        if ($request->hasFile('logo')) {
            if ($branch->logo) Storage::disk('public')->delete($branch->logo);
            $data['logo'] = $request->file('logo')->store('branch-logos', 'public');
        }
        $branch->update($data);
        return redirect()->route('branches.index')->with('success', 'Branch updated!');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch deleted.');
    }
}
