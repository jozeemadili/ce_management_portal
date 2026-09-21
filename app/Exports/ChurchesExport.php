<?php

namespace App\Exports;

use App\Models\Church;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ChurchesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $churches;

    public function __construct(Collection $churches)
    {
        $this->churches = $churches;
    }

    public function collection()
    {
        return $this->churches;
    }

    public function headings(): array
    {
        return [
            '#',
            'Church Name',
            'Designation',
            'Parent Church',
            'Head of Church',
            'Location',
            'Status',
            'Members',
            'Date Registered',
        ];
    }

    public function map($church): array
    {
        static $row = 0;
        $row++;

        $pastor = optional($church->current_head)->member;

        return [
            $row,
            $church->name,
            optional($church->church_designation)->name,
            optional($church->church)->name ?? 'ROOT',
            $pastor ? trim($pastor->first_name . ' ' . $pastor->last_name) : 'Not Assigned',
            $church->physical_location,
            $church->status,
            $church->members_count ?? $church->members()->count(),
            optional($church->created_at)->format('d M Y'),
        ];
    }

    public function title(): string
    {
        return 'Churches';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 18,
            'D' => 30,
            'E' => 26,
            'F' => 30,
            'G' => 12,
            'H' => 10,
            'I' => 16,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E5AAC'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:I1')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:I' . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G1:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
