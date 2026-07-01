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
                'employee_id'      => '0378',
                'first_name'       => 'Md. Rakib',
                'last_name'        => 'Hossain',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0378',
            ],
            [
                'employee_id'      => '0949',
                'first_name'       => 'Gulap Chandra',
                'last_name'        => 'Shil',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0949',
            ],
            [
                'employee_id'      => '1089',
                'first_name'       => 'Md.',
                'last_name'        => 'Mostakim',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '1089',
            ],
            [
                'employee_id'      => '0643',
                'first_name'       => 'Subrata',
                'last_name'        => 'Karmoker',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0643',
            ],
            [
                'employee_id'      => '0977',
                'first_name'       => 'Md. Rubel',
                'last_name'        => 'Mia',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0977',
            ],
            [
                'employee_id'      => '0995',
                'first_name'       => 'Jewel',
                'last_name'        => 'Mia',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0995',
            ],
            [
                'employee_id'      => '1081',
                'first_name'       => 'Md. Alimur',
                'last_name'        => 'Reza',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '1081',
            ],
            [
                'employee_id'      => '1005',
                'first_name'       => 'Mohammad Arman',
                'last_name'        => 'Rana',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '1005',
            ],
            [
                'employee_id'      => '0771',
                'first_name'       => 'Arun Kumar',
                'last_name'        => 'Das',
                'phone'            => '01968779400',
                'contact_number'   => '01968779400',
                'biometric_user_id'=> '0771',
                'old_names'        => ['Arun Kumear Das'],
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
                'first_name'       => 'Arafatul',
                'last_name'        => 'Islam',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0632',
                'old_names'        => ['Arafarul Islam'],
            ],
            [
                'employee_id'      => '0904',
                'first_name'       => 'Azizul',
                'last_name'        => 'Islam',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '0904',
            ],
            [
                'employee_id'      => '1013',
                'first_name'       => 'Md. Abid',
                'last_name'        => 'Hasan',
                'phone'            => null,
                'contact_number'   => null,
                'biometric_user_id'=> '1013',
                'old_employee_ids' => ['1073'],
            ],
        ];

        foreach ($employees as $data) {
            // Find existing employee
            $employee = null;
            
            // 1. Search by current employee_id
            $employee = Employee::where('employee_id', $data['employee_id'])->first();
            
            // 2. Search by old employee ids if not found
            if (! $employee && !empty($data['old_employee_ids'])) {
                $employee = Employee::whereIn('employee_id', $data['old_employee_ids'])->first();
            }
            
            // 3. Search by name or old names if not found
            if (! $employee) {
                $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
                $searchNames = array_merge([$fullName], $data['old_names'] ?? []);
                $employee = Employee::whereIn('name', $searchNames)->first();
            }

            // Generate login email
            $email = strtolower(
                str_replace([' ', '.'], ['', ''], $data['first_name']) .
                '.' .
                strtolower($data['last_name']) .
                '@hrm.com'
            );

            if ($employee) {
                // Update existing employee
                $employee->update([
                    'employee_id'       => $data['employee_id'],
                    'first_name'        => $data['first_name'],
                    'last_name'         => $data['last_name'],
                    'phone'             => $data['phone'] ?? $employee->phone,
                    'contact_number'    => $data['contact_number'] ?? $employee->contact_number,
                    'biometric_user_id' => $data['biometric_user_id'],
                ]);
                
                // If they have a user, update the user name and email
                if ($employee->user) {
                    $employee->user->update([
                        'name'  => trim($data['first_name'] . ' ' . $data['last_name']),
                        'email' => $email,
                    ]);
                }
                
                $this->command->info("✅ Updated: {$data['first_name']} {$data['last_name']} (ID: {$data['employee_id']})");
            } else {
                // Create new
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
                        'password'  => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'is_active' => true,
                    ]
                );
                if (! $user->hasRole('staff')) {
                    $user->assignRole('staff');
                }

                Employee::create([
                    'employee_id'      => $data['employee_id'],
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
                ]);

                $this->command->info("✅ Created: {$data['first_name']} {$data['last_name']} (ID: {$data['employee_id']})");
            }
        }

        $this->command->info("🎉 Employee import/update completed.");
    }
}
