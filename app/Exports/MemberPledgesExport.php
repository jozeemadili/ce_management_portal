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

class MemberPledgesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
            '#', 'Member', 'Church', 'Campaign', 'Pledge Reference', 'Amount', 'Frequency',
            'Fulfilled', 'Outstanding', 'Status', 'Source',
        ];
    }

    public function map($pledge): array
    {
        static $row = 0;
        $row++;

        $member = $pledge->member;

        return [
            $row,
            $member ? trim($member->first_name . ' ' . $member->last_name) : '',
            optional(optional($member)->church)->name,
            optional($pledge->campaign)->name,
            $pledge->pledge_reference,
            number_format($pledge->amount, 2),
            ucfirst(str_replace('_', ' ', $pledge->frequency)),
            number_format($pledge->totalFulfilled(), 2),
            number_format($pledge->outstanding(), 2),
            ucfirst(str_replace('_', ' ', $pledge->status)),
            $pledge->source === 'staff' ? 'Staff Recorded' : 'Member',
        ];
    }

    public function title(): string
    {
        return 'Member Pledges';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 26, 'C' => 26, 'D' => 26, 'E' => 16, 'F' => 16,
            'G' => 12, 'H' => 14, 'I' => 14, 'J' => 16, 'K' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5AAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:K1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:K' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
