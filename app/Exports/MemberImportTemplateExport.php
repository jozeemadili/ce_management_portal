<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Blank template for Member Management's bulk upload: the heading row the
 * importer expects plus one example row to overwrite.
 */
class MemberImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public const HEADINGS = [
        'Title',
        'First Name',
        'Last Name',
        'Phone',
        'Email',
        'Gender',
        'Date of Birth',
        'Foundation Classes',
        'Foundation Classes Date',
        'Baptism Status',
        'Baptism Date',
        'Marriage Status',
        'Marriage Date',
        'KingsChat Username',
    ];

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function array(): array
    {
        return [
            ['Brother', 'John', 'Mushi', '0712345678', 'john@example.com', 'male', '1990-05-14', 'yes', '2024-03-10', 'yes', '2024-06-02', 'married', '2018-12-01', 'johnmushi'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function columnWidths(): array
    {
        return array_combine(range('A', 'N'), array_fill(0, 14, 20));
    }

    public function title(): string
    {
        return 'Members';
    }
}
