<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = Setting::getGroup('general');
        return view('settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $keys = ['company_name', 'company_tagline', 'company_address', 'company_phone', 'company_email', 'currency_symbol', 'currency_code'];
        foreach ($keys as $key) {
            if ($request->has($key)) Setting::set($key, $request->$key, 'general');
        }
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            Setting::set('company_logo', $path, 'general');
        }
        return back()->with('success', 'General settings saved!');
    }

    public function leave()
    {
        $leaveTypes = LeaveType::all();
        $settings   = Setting::getGroup('leave');
        return view('settings.leave', compact('leaveTypes', 'settings'));
    }

    public function updateLeave(Request $request)
    {
        Setting::set('carry_forward_leave', $request->carry_forward_leave ? '1' : '0', 'leave');
        Setting::set('leave_auto_carry',    $request->leave_auto_carry    ? '1' : '0', 'leave');
        return back()->with('success', 'Leave settings saved!');
    }

    public function storeLeaveType(Request $request)
    {
        $request->validate(['name' => 'required', 'days_per_year' => 'required|integer']);
        LeaveType::create($request->only('name', 'code', 'days_per_year', 'carry_forward', 'is_paid', 'color', 'is_active'));
        return back()->with('success', 'Leave type added!');
    }
}
