<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeeSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'EMP-0001','John','Doe','john@example.com','01700000000','01700000001',
                'Head Office','Sales','Sales Executive','General Shift','1001',
                'Male','1995-06-15','2024-01-01','25000','active',
                'B+','123 Main St, Dhaka','12345678901234',
                'Dutch Bangla Bank','1234567890',
            ],
            [
                'EMP-0002','Jane','Smith','jane@example.com','01800000000','01800000001',
                'Head Office','HR','HR Officer','General Shift','1002',
                'Female','1998-03-20','2024-02-01','22000','active',
                'O+','456 Park Ave, Chittagong','98765432109876',
                'BRAC Bank','0987654321',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'employee_id','first_name','last_name','email','phone','contact_number',
            'branch','department','designation','shift','punch_id',
            'gender','date_of_birth','joining_date','basic_salary','status',
            'blood_group','address','nid','bank_name','bank_account',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style headings
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6366F1']],
        ]);

        // Add comments/instructions
        $sheet->setCellValue('A3', '*** Fill employee data from row 4 onwards. Delete sample rows 2 & 3 before importing. ***');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('FFEF4444'))
        );

        return [];
    }
}
