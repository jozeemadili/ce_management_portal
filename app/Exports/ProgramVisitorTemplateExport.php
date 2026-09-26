<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template for uploading first-time visitors on the programs registration
 * desk: the heading row the upload expects plus one example row.
 */
class ProgramVisitorTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function headings(): array
    {
        return ['First Name', 'Last Name', 'Phone', 'Gender', 'Invited By'];
    }

    public function array(): array
    {
        return [
            ['Neema', 'John', '0712345678', 'female', 'Grace Mushi'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 20, 'C' => 18, 'D' => 12, 'E' => 24];
    }

    public function title(): string
    {
        return 'Visitors';
    }
}
