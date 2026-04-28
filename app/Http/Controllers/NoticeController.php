<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('creator', 'branch')->latest()->paginate(20);
        return view('notices.index', compact('notices'));
    }

    public function create()
    {
        $branches    = Branch::all();
        $departments = Department::all();
        return view('notices.create', compact('branches', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'body' => 'required', 'type' => 'required']);
        Notice::create(array_merge(
            $request->only('title', 'body', 'type', 'audience', 'branch_id', 'department_id', 'published_at', 'expires_at', 'is_published'),
            ['created_by' => Auth::id()]
        ));
        return redirect()->route('notices.index')->with('success', 'Notice published!');
    }

    public function edit(Notice $notice)
    {
        $branches    = Branch::all();
        $departments = Department::all();
        return view('notices.edit', compact('notice', 'branches', 'departments'));
    }

    public function update(Request $request, Notice $notice)
    {
        $notice->update($request->only('title', 'body', 'type', 'audience', 'branch_id', 'department_id', 'published_at', 'expires_at', 'is_published'));
        return redirect()->route('notices.index')->with('success', 'Notice updated!');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return back()->with('success', 'Notice deleted.');
    }
}
