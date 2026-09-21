<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProgramsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $programs;

    public function __construct(Collection $programs)
    {
        $this->programs = $programs;
    }

    public function collection()
    {
        return $this->programs;
    }

    public function headings(): array
    {
        return [
            '#', 'Program', 'Category', 'Classification', 'Scope', 'Status',
            'Access', 'Fee', 'Registered', 'Attendances',
        ];
    }

    public function map($program): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $program->name,
            ucfirst(str_replace('_', ' ', $program->category)),
            ucfirst($program->classification),
            $program->scope === 'global' ? 'Global' : ucfirst($program->scope),
            ucfirst($program->status),
            $program->access_type === 'free' ? 'Free' : 'Paid',
            $program->access_type === 'free' ? '-' : $program->currency . ' ' . number_format($program->registration_fee, 2),
            $program->registrations_count ?? $program->registrations()->count(),
            $program->attendances_count ?? $program->attendances()->count(),
        ];
    }

    public function title(): string
    {
        return 'Programs';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 30, 'C' => 16, 'D' => 14, 'E' => 14, 'F' => 12,
            'G' => 10, 'H' => 16, 'I' => 12, 'J' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5AAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:J1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:J' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I1:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
