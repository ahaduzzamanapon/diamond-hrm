<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SpecificEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        // Use Head Office branch (first branch)
        $branch = Branch::where('code', 'HO')->first();
        if (! $branch) {
            $branch = Branch::first();
        }

        // Use any available department/designation
        $department  = Department::where('branch_id', $branch->id)->first();
        $designation = Designation::where('department_id', $department->id)->first();
        $shift       = Shift::first();

        $employees = [
            [
                'employee_id'      => '0771',
                'first_name'       => 'Arun Kumear',
                'last_name'        => 'Das',
                'phone'            => '01968779400',
                'contact_number'   => '01968779400',
                'biometric_user_id'=> '0771',
            ],
            [
                'employee_id'      => '0808',
                'first_name'       => 'Imran',
                'last_name'        => 'Hossain',
                'phone'            => '01963333000',
                'contact_number'   => '01963333000',
                'biometric_user_id'=> '0808',
            ],
            [
                'employee_id'      => '0632',
                'first_name'       => 'Arafarul',
                'last_name'        => 'Islam',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0632',
            ],
            [
                'employee_id'      => '1073',
                'first_name'       => 'Md. Abid',
                'last_name'        => 'Hasan',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '1073',
            ],
        ];

        foreach ($employees as $data) {
            // Create a login user account for each employee
            $email = strtolower(
                str_replace([' ', '.'], ['', ''], $data['first_name']) .
                '.' .
                strtolower($data['last_name']) .
                '@hrm.com'
            );

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
                    'password'  => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ]
            );
            $user->assignRole('staff');

            Employee::firstOrCreate(
                ['employee_id' => $data['employee_id']],
                [
                    'user_id'          => $user->id,
                    'branch_id'        => $branch->id,
                    'department_id'    => $department->id,
                    'designation_id'   => $designation->id,
                    'shift_id'         => $shift?->id,
                    'first_name'       => $data['first_name'],
                    'last_name'        => $data['last_name'],
                    'email'            => $email,
                    'phone'            => $data['phone'],
                    'contact_number'   => $data['contact_number'],
                    'gender'           => 'male',
                    'joining_date'     => now()->format('Y-m-d'),
                    'basic_salary'     => 0,
                    'biometric_user_id'=> $data['biometric_user_id'],
                    'status'           => 'active',
                ]
            );

            $this->command->info("✅ Added: {$data['first_name']} {$data['last_name']} (ID: {$data['employee_id']})");
        }

        $this->command->table(
            ['Employee ID', 'Name', 'Mobile', 'Login Email', 'Password'],
            [
                ['0771', 'Arun Kumear Das',  '01968779400', 'arunkumear.das@hrm.com',  'password'],
                ['0808', 'Imran Hossain',    '01963333000', 'imran.hossain@hrm.com',   'password'],
                ['0632', 'Arafarul Islam',   '-',           'arafarul.islam@hrm.com',  'password'],
                ['1073', 'Md. Abid Hasan',   '-',           'mdabid.hasan@hrm.com',    'password'],
            ]
        );
    }
}
