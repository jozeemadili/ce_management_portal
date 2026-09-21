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

class StaffRecordedPledgesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $pledges;

    public function __construct(Collection $pledges)
    {
        $this->pledges = $pledges;
    }

    public function collection()
    {
        return $this->pledges;
    }

    public function headings(): array
    {
        return [
            '#', 'Staff Member', 'Member', 'Church', 'Campaign', 'Amount', 'Date/Time',
        ];
    }

    public function map($pledge): array
    {
        static $row = 0;
        $row++;

        $member = $pledge->member;
        $staff = $pledge->recorder;

        return [
            $row,
            $staff ? trim($staff->first_name . ' ' . $staff->last_name) : '',
            $member ? trim($member->first_name . ' ' . $member->last_name) : '',
            optional(optional($member)->church)->name,
            optional($pledge->campaign)->name,
            number_format($pledge->amount, 2),
            optional($pledge->created_at)->format('d M Y, H:i'),
        ];
    }

    public function title(): string
    {
        return 'Staff Recorded Pledges';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 24, 'C' => 26, 'D' => 26, 'E' => 26, 'F' => 16, 'G' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5AAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:G' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
