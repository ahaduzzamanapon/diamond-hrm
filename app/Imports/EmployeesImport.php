<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $branch = Branch::where('name', $row['branch'])->orWhere('code', $row['branch'])->first();
                $dept   = Department::where('name', $row['department'])->first();
                $desig  = Designation::where('name', $row['designation'])->first();
                $shift  = Shift::where('name', $row['shift'] ?? 'General')->first();

                if (!$branch || !$dept || !$desig) {
                    $this->errors[] = "Row " . ($index + 2) . ": Branch/Department/Designation not found – skipped.";
                    continue;
                }

                $employeeId = $row['employee_id'] ?? ('EMP-' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT));

                // Create or update User account
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $row['email']],
                    [
                        'name'     => trim($row['first_name'] . ' ' . ($row['last_name'] ?? '')),
                        'password' => Hash::make($row['password'] ?? 'password'),
                        'branch_id'=> $branch->id,
                    ]
                );
                $user->assignRole($row['role'] ?? 'staff');

                Employee::updateOrCreate(
                    ['employee_id' => $employeeId],
                    [
                        'user_id'        => $user->id,
                        'branch_id'      => $branch->id,
                        'department_id'  => $dept->id,
                        'designation_id' => $desig->id,
                        'shift_id'       => $shift?->id,
                        'first_name'     => $row['first_name'],
                        'last_name'      => $row['last_name'] ?? null,
                        'email'          => $row['email'] ?? null,
                        'phone'          => $row['phone'] ?? null,
                        'contact_number' => $row['contact_number'] ?? $row['phone'] ?? null,
                        'gender'         => strtolower($row['gender'] ?? 'male'),
                        'date_of_birth'  => $this->parseDate($row['date_of_birth'] ?? null),
                        'joining_date'   => $this->parseDate($row['joining_date'] ?? now()),
                        'biometric_user_id' => $row['punch_id'] ?? null,
                        'basic_salary'   => $row['basic_salary'] ?? 0,
                        'address'        => $row['address'] ?? null,
                        'status'         => strtolower($row['status'] ?? 'active'),
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
