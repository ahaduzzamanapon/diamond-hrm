<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('employees')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'start_time' => 'required', 'end_time' => 'required']);
        Shift::create(array_merge(
            $request->only('name', 'start_time', 'end_time', 'grace_minutes', 'break_minutes', 'is_active'),
            [
                'sunday'    => $request->boolean('sunday'),
                'monday'    => $request->boolean('monday'),
                'tuesday'   => $request->boolean('tuesday'),
                'wednesday' => $request->boolean('wednesday'),
                'thursday'  => $request->boolean('thursday'),
                'friday'    => $request->boolean('friday'),
                'saturday'  => $request->boolean('saturday'),
            ]
        ));
        return back()->with('success', 'Shift added!');
    }

    public function update(Request $request, Shift $shift)
    {
        $shift->update(array_merge(
            $request->only('name', 'start_time', 'end_time', 'grace_minutes', 'break_minutes', 'is_active'),
            [
                'sunday'    => $request->boolean('sunday'),
                'monday'    => $request->boolean('monday'),
                'tuesday'   => $request->boolean('tuesday'),
                'wednesday' => $request->boolean('wednesday'),
                'thursday'  => $request->boolean('thursday'),
                'friday'    => $request->boolean('friday'),
                'saturday'  => $request->boolean('saturday'),
            ]
        ));
        return back()->with('success', 'Shift updated!');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift deleted.');
    }
}
