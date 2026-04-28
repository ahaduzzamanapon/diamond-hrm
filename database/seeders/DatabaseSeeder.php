<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────
        $roles = ['super-admin','hr-admin','hr','branch-manager','staff'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── Permissions ────────────────────────────────────────────────────
        $permissions = [
            'view_all_branches',
            'manage_employees','view_employees',
            'manage_employees','view_employees',
            'manage_advance_salary','view_advance_salary',
            'manage_attendance','view_attendance',
            'manage_leaves','approve_leaves','view_leaves',
            'manage_shifts','manage_holidays',
            'manage_notices','manage_settings',
            'manage_biometric','manage_roles',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        Role::findByName('super-admin')->givePermissionTo($permissions);
        Role::findByName('hr-admin')->givePermissionTo([
            'view_all_branches','manage_employees','view_employees',
            'manage_advance_salary','view_advance_salary',
            'manage_attendance','view_attendance',
            'manage_leaves','approve_leaves','view_leaves',
            'manage_shifts','manage_holidays','manage_notices',
        ]);
        Role::findByName('hr')->givePermissionTo([
            'view_employees','view_attendance','manage_attendance',
            'approve_leaves','view_leaves','view_advance_salary',
        ]);
        Role::findByName('branch-manager')->givePermissionTo([
            'view_employees','view_attendance','approve_leaves','view_leaves',
            'manage_notices',
        ]);
        Role::findByName('staff')->givePermissionTo([
            'view_leaves','view_attendance','view_advance_salary',
        ]);

        // ── Branches ───────────────────────────────────────────────────────
        $branchesData = [
            ['code' => 'HO', 'name' => 'Head Office', 'address' => 'Dhaka, Bangladesh', 'phone' => '01700000000'],
            ['code' => 'CTG', 'name' => 'Chattogram Branch', 'address' => 'Agrabad, Chattogram', 'phone' => '01800000000'],
            ['code' => 'SYL', 'name' => 'Sylhet Branch', 'address' => 'Zindabazar, Sylhet', 'phone' => '01900000000'],
        ];
        $createdBranches = [];
        foreach ($branchesData as $b) {
            $createdBranches[] = Branch::firstOrCreate(
                ['code' => $b['code']],
                array_merge($b, ['is_active' => true])
            );
        }
        $branch = $createdBranches[0]; // Main Branch HO for generic users

        // ── Departments & Designations Per Branch ─────────────────────────
        $depts = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance',          'code' => 'FIN'],
            ['name' => 'Sales',            'code' => 'SALES'],
            ['name' => 'IT',               'code' => 'IT'],
            ['name' => 'Operations',       'code' => 'OPS'],
        ];
        
        foreach ($createdBranches as $b) {
            foreach ($depts as $dept) {
                $code = $dept['code'] . '-' . $b->code;
                $d = Department::firstOrCreate(
                    ['code' => $code, 'branch_id' => $b->id], 
                    ['name' => $dept['name'], 'code' => $code, 'branch_id' => $b->id]
                );
                Designation::firstOrCreate(['name' => 'Manager', 'department_id' => $d->id], ['department_id' => $d->id]);
                Designation::firstOrCreate(['name' => 'Officer', 'department_id' => $d->id], ['department_id' => $d->id]);
                Designation::firstOrCreate(['name' => 'Executive','department_id' => $d->id], ['department_id' => $d->id]);
            }
        }

        // ── Shifts ─────────────────────────────────────────────────────────
        $shift = Shift::firstOrCreate(['name' => 'General Shift'], [
            'start_time' => '09:00:00', 'end_time' => '17:00:00',
            'grace_minutes' => 15, 'break_minutes' => 60,
            'monday' => true, 'tuesday' => true, 'wednesday' => true,
            'thursday' => true, 'friday' => true,
            'saturday' => false, 'sunday' => false,
        ]);
        Shift::firstOrCreate(['name' => 'Morning Shift'], [
            'start_time' => '07:00:00', 'end_time' => '15:00:00',
            'grace_minutes' => 10, 'break_minutes' => 30,
            'monday' => true, 'tuesday' => true, 'wednesday' => true,
            'thursday' => true, 'friday' => true, 'saturday' => true,
            'sunday' => false,
        ]);

        // ── Leave Types ────────────────────────────────────────────────────
        $leaveTypes = [
            ['name'=>'Casual Leave',   'code'=>'CL', 'days_per_year'=>10, 'is_paid'=>true,  'color'=>'#6366f1'],
            ['name'=>'Sick Leave',     'code'=>'SL', 'days_per_year'=>14, 'is_paid'=>true,  'color'=>'#ef4444'],
            ['name'=>'Annual Leave',   'code'=>'AL', 'days_per_year'=>18, 'is_paid'=>true,  'color'=>'#10b981', 'carry_forward'=>true],
            ['name'=>'Maternity Leave','code'=>'ML', 'days_per_year'=>90, 'is_paid'=>true,  'color'=>'#f59e0b'],
            ['name'=>'Unpaid Leave',   'code'=>'UL', 'days_per_year'=>30, 'is_paid'=>false, 'color'=>'#6b7280'],
        ];
        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['code' => $lt['code']], $lt);
        }

        // ── Default Settings ───────────────────────────────────────────────
        $settings = [
            ['key'=>'company_name',       'value'=>'Diamond World',          'group'=>'general'],
            ['key'=>'company_tagline',    'value'=>'HRM Management System',  'group'=>'general'],
            ['key'=>'company_address',    'value'=>'Dhaka, Bangladesh',      'group'=>'general'],
            ['key'=>'company_phone',      'value'=>'+880 1700-000000',       'group'=>'general'],
            ['key'=>'company_email',      'value'=>'info@diamondworld.com',  'group'=>'general'],
            ['key'=>'currency_symbol',    'value'=>'৳',                      'group'=>'general'],
            ['key'=>'currency_code',      'value'=>'BDT',                    'group'=>'general'],

            ['key'=>'carry_forward_leave','value'=>'1',                      'group'=>'leave'],
            ['key'=>'leave_auto_carry',   'value'=>'0',                      'group'=>'leave'],
        ];
        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }

        // ── Super Admin User ───────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrm.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );
        $admin->assignRole('super-admin');

        // ── HR Admin ───────────────────────────────────────────────────────
        $hrAdmin = User::firstOrCreate(
            ['email' => 'hradmin@hrm.com'],
            [
                'name'      => 'HR Admin',
                'password'  => Hash::make('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );
        $hrAdmin->assignRole('hr-admin');

        // ── Global Employees Array Tracker ─────────────────────────────────
        $employees = [];

        // ── Branch Managers ────────────────────────────────────────────────
        foreach ($createdBranches as $idx => $br) {
            $prefix = ['dhaka', 'ctg', 'sylhet'][$idx];
            $mgrUser = User::firstOrCreate(
                ['email' => "manager.{$prefix}@hrm.com"],
                [
                    'name'      => "Manager {$prefix} HQ",
                    'password'  => Hash::make('password'),
                    'branch_id' => $br->id,
                    'is_active' => true,
                ]
            );
            $mgrUser->assignRole('branch-manager');
            
            $d = Department::where('branch_id', $br->id)->inRandomOrder()->first();
            $desig = Designation::where('department_id', $d->id)->where('name', 'Manager')->first();
            
            $emp = Employee::firstOrCreate(
                ['employee_id' => "MGR-00" . ($idx + 1)],
                [
                    'user_id'        => $mgrUser->id,
                    'branch_id'      => $br->id,
                    'department_id'  => $d->id,
                    'designation_id' => $desig->id,
                    'shift_id'       => $shift->id,
                    'first_name'     => "Manager",
                    'last_name'      => ucfirst($prefix),
                    'email'          => $mgrUser->email,
                    'phone'          => '0160000000' . $idx,
                    'contact_number' => '0160000000' . $idx,
                    'gender'         => 'male',
                    'joining_date'   => '2023-01-01',
                    'basic_salary'   => 40000,
                    'house_rent_allowance' => 16000,
                    'medical_allowance'    => 4000,
                    'transport_allowance'  => 2000,
                    'status'         => 'active',
                    'biometric_user_id' => (string)(2000 + $idx),
                ]
            );
            $employees[] = $emp;
        }

        // ── Sample Staff Employee ──────────────────────────────────────────
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@hrm.com'],
            [
                'name'      => 'John Doe',
                'password'  => Hash::make('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
            ]
        );
        $staffUser->assignRole('staff');

        $hrDept  = Department::where('code', "HR-HO")->first();
        $hrDesig = Designation::where('department_id', $hrDept->id)->where('name','Officer')->first();

        // Standard HR Employee
        $mainEmp = Employee::firstOrCreate(
            ['employee_id' => 'EMP-0001'],
            [
                'user_id'        => $staffUser->id,
                'branch_id'      => $branch->id,
                'department_id'  => $hrDept->id,
                'designation_id' => $hrDesig->id,
                'shift_id'       => $shift->id,
                'first_name'     => 'John',
                'last_name'      => 'Doe',
                'email'          => 'staff@hrm.com',
                'phone'          => '01700000001',
                'contact_number' => '01700000001',
                'gender'         => 'male',
                'joining_date'   => '2024-01-01',
                'basic_salary'   => 25000,
                'house_rent_allowance' => 10000,
                'medical_allowance'    => 2000,
                'transport_allowance'  => 1500,
                'status'         => 'active',
                'biometric_user_id' => '1001',
            ]
        );
        $employees[] = $mainEmp;

        // ── Seed 50 Random Employees across Branches ────────────────────────
        $faker = \Faker\Factory::create();
        
        for ($i = 2; $i <= 51; $i++) {
            $fName = $faker->firstName;
            $lName = $faker->lastName;
            $email = strtolower($fName . '.' . $lName . $i . '@hrm.com');
            
            // Randomly assign to a branch
            $randBranch = collect($createdBranches)->random();
            
            $user = User::create([
                'name'      => $fName . ' ' . $lName,
                'email'     => $email,
                'password'  => Hash::make('password'),
                'branch_id' => $randBranch->id,
                'is_active' => true,
            ]);
            $user->assignRole('staff');

            $randDept = Department::where('branch_id', $randBranch->id)->inRandomOrder()->first();
            $randDesig = Designation::where('department_id', $randDept->id)->inRandomOrder()->first();

            $basePay = $faker->randomElement([15000, 20000, 25000, 30000, 35000, 40000, 50000]);

            $emp = Employee::create([
                'employee_id'    => 'EMP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id'        => $user->id,
                'branch_id'      => $randBranch->id,
                'department_id'  => $randDept->id,
                'designation_id' => $randDesig->id,
                'shift_id'       => $shift->id,
                'first_name'     => $fName,
                'last_name'      => $lName,
                'email'          => $email,
                'phone'          => $faker->numerify('01#########'),
                'contact_number' => $faker->numerify('01#########'),
                'gender'         => $faker->randomElement(['male', 'female']),
                'joining_date'   => $faker->dateTimeBetween('-2 years', '-1 months')->format('Y-m-d'),
                'basic_salary'   => $basePay,
                'house_rent_allowance' => $basePay * 0.4,
                'medical_allowance'    => $basePay * 0.1,
                'transport_allowance'  => $basePay * 0.05,
                'status'         => 'active',
                'biometric_user_id' => (string)(1000 + $i),
            ]);
            $employees[] = $emp;
        }

        // ── Seed Attendance for March 2026 ─────────────────────────────────
        $monthStr = '2026-03';
        $carbonMonth = \Carbon\Carbon::parse($monthStr . '-01');
        $daysInMonth = $carbonMonth->daysInMonth;
        
        $holidays = ['2026-03-17', '2026-03-26']; // Randomly picked public holidays for March 2026

        foreach ($employees as $emp) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = \Carbon\Carbon::create(2026, 3, $day);
                $dateStr = $currentDate->format('Y-m-d');
                
                // Defaults
                $status = 'present';
                $inTime = '09:00:00';
                $outTime = '17:00:00';
                $workingMin = 480;
                $lateMin = 0;
                
                if (in_array($dateStr, $holidays)) {
                    $status = 'holiday';
                    $inTime = null;
                    $outTime = null;
                    $workingMin = 0;
                } elseif ($currentDate->isFriday() || $currentDate->isSaturday()) {
                    $status = 'weekend';
                    $inTime = null;
                    $outTime = null;
                    $workingMin = 0;
                } else {
                    // Randomize working days
                    $rand = rand(1, 100);
                    if ($rand <= 5) {
                        $status = 'absent';
                        $inTime = null;
                        $outTime = null;
                        $workingMin = 0;
                    } elseif ($rand <= 10) {
                        $status = 'leave';
                        $inTime = null;
                        $outTime = null;
                        $workingMin = 0;
                    } elseif ($rand <= 25) {
                        $status = 'late';
                        $inTime = '09:' . rand(16, 59) . ':00'; // After grace period
                        $lateMin = \Carbon\Carbon::parse($inTime)->diffInMinutes(\Carbon\Carbon::parse('09:00:00'));
                        $workingMin = 480 - $lateMin;
                    } else {
                        // Regular present
                        // Could be slightly early or exactly on time
                        $inTime = '08:' . rand(45, 59) . ':00';
                    }
                }

                \App\Models\Attendance::create([
                    'employee_id' => $emp->id,
                    'date'        => $dateStr,
                    'in_time'     => $inTime,
                    'out_time'    => $outTime,
                    'working_minutes' => $workingMin,
                    'late_minutes'    => $lateMin,
                    'early_out_minutes' => 0,
                    'overtime_minutes'  => 0,
                    'status'      => $status,
                    'source'      => 'biometric',
                    'entered_by'  => $admin->id
                ]);
            }
        }

        $this->command->info('✅ HRM Seeder completed fully with 50 synthetic employees and attendance tracking. Logins:');
        $this->command->table(
            ['Role','Email','Password'],
            [
                ['Super Admin','admin@hrm.com','password'],
                ['HR Admin','hradmin@hrm.com','password'],
                ['Staff','staff@hrm.com','password'],
            ]
        );
    }
}
