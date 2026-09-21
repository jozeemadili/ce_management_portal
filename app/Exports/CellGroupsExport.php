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

class CellGroupsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $cellGroups;

    public function __construct(Collection $cellGroups)
    {
        $this->cellGroups = $cellGroups;
    }

    public function collection()
    {
        return $this->cellGroups;
    }

    public function headings(): array
    {
        return [
            '#',
            'Cell Name',
            'Church',
            'Description',
            'Members',
            'Cell Leader',
            'Assistant Leader',
        ];
    }

    public function map($cell): array
    {
        static $row = 0;
        $row++;

        $leader = $cell->members->first(fn($m) => $m->pivot->role === 'CELL_LEADER');
        $assistant = $cell->members->first(fn($m) => $m->pivot->role === 'ASSISTANT_CELL_LEADER');

        return [
            $row,
            $cell->name,
            optional($cell->church)->name,
            $cell->description,
            $cell->members_count ?? $cell->members->count(),
            $leader ? trim($leader->first_name . ' ' . $leader->last_name) : 'Not Assigned',
            $assistant ? trim($assistant->first_name . ' ' . $assistant->last_name) : 'Not Assigned',
        ];
    }

    public function title(): string
    {
        return 'Cell Groups';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 26,
            'C' => 26,
            'D' => 30,
            'E' => 10,
            'F' => 24,
            'G' => 24,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
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

        $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:G' . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E1:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
