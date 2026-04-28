<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Exports\EmployeeSampleExport;
use App\Imports\EmployeesImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeQualification;
use App\Models\EmployeeSocial;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Employee::with(['branch','department','designation','shift'])
            ->forUser($user);

        if ($request->filled('branch_id'))     $query->where('branch_id',     $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('status'))        $query->where('status',        $request->status);
        if ($request->filled('search'))        $query->where(fn($q) => $q->where('name','like',"%{$request->search}%")->orWhere('employee_id','like',"%{$request->search}%")->orWhere('email','like',"%{$request->search}%"));

        $employees   = $query->orderBy('employee_id')->paginate(20)->withQueryString();
        $branches    = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id',$user->branch_id))->get();

        return view('employees.index', compact('employees','branches','departments'));
    }

    public function create()
    {
        $user         = Auth::user();
        $branches     = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments  = Department::when(!$user->hasPermissionTo('view_all_branches'), fn($q) => $q->where('branch_id',$user->branch_id))->get();
        $designations = Designation::all();
        $shifts       = Shift::where('is_active',true)->get();
        $leaveTypes   = LeaveType::where('is_active',true)->get();
        $roles        = Role::all();
        $leads        = Employee::where('employee_type','lead')->where('status','active')->get();
        return view('employees.create', compact('branches','departments','designations','shifts','leaveTypes','roles','leads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:100',
            'branch_id'       => 'required|exists:branches,id',
            'department_id'   => 'required|exists:departments,id',
            'designation_id'  => 'required|exists:designations,id',
            'joining_date'    => 'required|date',
            'email'           => 'nullable|email|unique:users,email',
            'username'        => 'nullable|string|unique:employees,username',
            'password'        => 'nullable|min:6|confirmed',
            'basic_salary'    => 'nullable|numeric|min:0',
            'contact_number'  => 'required|string',
            'role'            => 'required|exists:roles,name',
            'photo'           => 'nullable|image|max:2048',
            'note_file'       => 'nullable|file|max:5120',
            'status'          => 'required|in:active,inactive,terminated',
        ]);

        DB::transaction(function () use ($request) {
            // Generate Employee ID
            $lastId = Employee::max('id') ?? 0;
            $employeeId = 'EMP-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // Handle photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employees/photos','public');
            }
            $notePath = null;
            if ($request->hasFile('note_file')) {
                $notePath = $request->file('note_file')->store('employees/documents','public');
            }

            // Create User
            $user = null;
            if ($request->filled('email')) {
                $user = User::create([
                    'name'      => trim($request->first_name . ' ' . $request->last_name),
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password ?? 'password'),
                    'branch_id' => $request->branch_id,
                    'is_active' => true,
                ]);
                $user->assignRole($request->role);
            }

            // Create Employee
            $employee = Employee::create([
                'employee_id'           => $request->employee_id ?? $employeeId,
                'user_id'               => $user?->id,
                'branch_id'             => $request->branch_id,
                'department_id'         => $request->department_id,
                'designation_id'        => $request->designation_id,
                'shift_id'              => $request->shift_id,
                'first_name'            => $request->first_name,
                'last_name'             => $request->last_name,
                'email'                 => $request->email,
                'phone'                 => $request->phone,
                'contact_number'        => $request->contact_number,
                'username'              => $request->username,
                'gender'                => $request->gender ?? 'male',
                'date_of_birth'         => $request->date_of_birth,
                'joining_date'          => $request->joining_date,
                'probation_months'      => $request->probation_months ?? 0,
                'employee_type'         => $request->employee_or_lead ?? 'employee',
                'team_leader_id'        => $request->team_leader_id,
                'leave_type_id'         => $request->leave_category,
                'blood_group'           => $request->blood_group,
                'nid'                   => $request->nid,
                'address'               => $request->address,
                'permanent_address'     => $request->permanent_address,
                'biometric_user_id'     => $request->punch_id,
                'basic_salary'          => $request->basic_salary ?? 0,
                'house_rent_allowance'  => $request->house_rent_allowance ?? 0,
                'medical_allowance'     => $request->medical_allowance ?? 0,
                'transport_allowance'   => $request->transport_allowance ?? 0,
                'bank_name'             => $request->bank_name,
                'bank_account'          => $request->bank_account,
                'photo'                 => $photoPath,
                'note_file'             => $notePath,
                'remark'                => $request->remark,
                'status'                => $request->status,
            ]);

            // Emergency Contact
            if ($request->filled('ec_name')) {
                EmployeeEmergencyContact::create([
                    'employee_id' => $employee->id,
                    'name'        => $request->ec_name,
                    'relation'    => $request->ec_relation,
                    'phone'       => $request->ec_phone,
                    'address'     => $request->ec_address,
                ]);
            }
        });

        return redirect()->route('employees.index')->with('success','Employee added successfully!');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'branch','department','designation','shift','teamLeader',
            'emergencyContacts','social','documents','qualifications','contracts',
            'user','attendances' => fn($q) => $q->latest()->take(10),
            'leaves' => fn($q) => $q->with('leaveType')->latest()->take(5),
            'payrolls' => fn($q) => $q->latest()->take(6),
        ]);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $user         = Auth::user();
        $branches     = $user->hasPermissionTo('view_all_branches') ? Branch::all() : Branch::where('id',$user->branch_id)->get();
        $departments  = Department::where('branch_id', $employee->branch_id)->get();
        $designations = Designation::where('department_id', $employee->department_id)->get();
        $shifts       = Shift::where('is_active',true)->get();
        $leaveTypes   = LeaveType::where('is_active',true)->get();
        $roles        = Role::all();
        $leads        = Employee::where('employee_type','lead')->where('status','active')->where('id','!=',$employee->id)->get();
        $employee->load(['emergencyContacts','social','documents','qualifications','contracts']);
        return view('employees.edit', compact('employee','branches','departments','designations','shifts','leaveTypes','roles','leads'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'first_name'     => 'required|string|max:100',
            'branch_id'      => 'required|exists:branches,id',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date'   => 'required|date',
            'status'         => 'required|in:active,inactive,terminated',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $data = $request->except(['_token','_method','photo','note_file','role','password','password_confirmation']);

            if ($request->hasFile('photo')) {
                if ($employee->photo) Storage::disk('public')->delete($employee->photo);
                $data['photo'] = $request->file('photo')->store('employees/photos','public');
            }

            $employee->update($data);

            // Update role
            if ($employee->user && $request->filled('role')) {
                $employee->user->syncRoles([$request->role]);
            }

            // Update password
            if ($employee->user && $request->filled('password')) {
                $employee->user->update(['password' => Hash::make($request->password)]);
            }
        });

        return redirect()->route('employees.show',$employee)->with('success','Employee updated!');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success','Employee removed.');
    }

    // ── Import / Export ────────────────────────────────────────────────────

    public function importForm()
    {
        return view('employees.import');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv']);
        $import = new EmployeesImport;
        Excel::import($import, $request->file('file'));
        $msg = 'Import completed!';
        if (!empty($import->errors)) {
            return redirect()->route('employees.index')->with('warning', $msg)->with('import_errors', $import->errors);
        }
        return redirect()->route('employees.index')->with('success', $msg);
    }

    public function export(Request $request)
    {
        $query = Employee::with(['branch','department','designation','shift'])->forUser(Auth::user());
        if ($request->filled('branch_id'))     $query->where('branch_id',     $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('status'))        $query->where('status',        $request->status);
        return Excel::download(new EmployeesExport($query), 'employees_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadSample()
    {
        return Excel::download(new EmployeeSampleExport, 'employee_import_sample.xlsx');
    }

    // ── AJAX helpers ───────────────────────────────────────────────────────
    public function getDepartments(Request $request)
    {
        return response()->json(Department::where('branch_id', $request->branch_id)->where('is_active',true)->get(['id','name']));
    }

    public function getDesignations(Request $request)
    {
        return response()->json(Designation::where('department_id', $request->department_id)->where('is_active',true)->get(['id','name']));
    }
}
