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

class NewSoulsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $visitors;

    public function __construct(Collection $visitors)
    {
        $this->visitors = $visitors;
    }

    public function collection()
    {
        return $this->visitors;
    }

    public function headings(): array
    {
        return ['#', 'Name', 'Phone', 'Church', 'First Visit Program', 'First Visit Date', 'Invited By', 'Status'];
    }

    public function map($v): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            trim($v->first_name . ' ' . $v->last_name),
            $v->phone ?? '—',
            optional($v->church)->name ?? '—',
            optional($v->firstVisitProgram)->name ?? '—',
            optional($v->first_visit_date)->format('d M Y'),
            $v->invited_by ?? '—',
            ucfirst(str_replace('_', ' ', $v->follow_up_status)),
        ];
    }

    public function title(): string
    {
        return 'New Souls';
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 24, 'C' => 16, 'D' => 22, 'E' => 26, 'F' => 14, 'G' => 18, 'H' => 18];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5AAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:H' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
