<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private $query = null) {}

    public function collection()
    {
        return ($this->query ?? Employee::query())
            ->with(['branch','department','designation','shift'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID','First Name','Last Name','Email','Phone','Contact Number',
            'Branch','Department','Designation','Shift','Punch ID',
            'Gender','Date of Birth','Joining Date','Basic Salary','Status',
            'Blood Group','Address','NID','Bank Name','Bank Account',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->employee_id,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->contact_number,
            $employee->branch?->name,
            $employee->department?->name,
            $employee->designation?->name,
            $employee->shift?->name,
            $employee->biometric_user_id,
            ucfirst($employee->gender),
            $employee->date_of_birth?->format('Y-m-d'),
            $employee->joining_date?->format('Y-m-d'),
            $employee->basic_salary,
            ucfirst($employee->status),
            $employee->blood_group,
            $employee->address,
            $employee->nid,
            $employee->bank_name,
            $employee->bank_account,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6366F1']],
            ],
        ];
    }
}
