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

class PledgeContributionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $contributions;

    public function __construct(Collection $contributions)
    {
        $this->contributions = $contributions;
    }

    public function collection()
    {
        return $this->contributions;
    }

    public function headings(): array
    {
        return [
            '#', 'Pledge Ref', 'Member', 'Church', 'Amount', 'Date', 'Payment Reference', 'Method', 'Recorded By',
        ];
    }

    public function map($contribution): array
    {
        static $row = 0;
        $row++;

        $member = optional($contribution->pledge)->member;

        return [
            $row,
            optional($contribution->pledge)->pledge_reference,
            $member ? trim($member->first_name . ' ' . $member->last_name) : '',
            optional(optional($member)->church)->name,
            number_format($contribution->amount, 2),
            optional($contribution->payment_date)->format('d M Y'),
            $contribution->payment_reference,
            $contribution->payment_method,
            optional($contribution->recorder)->first_name,
        ];
    }

    public function title(): string
    {
        return 'Contributions';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 16, 'C' => 26, 'D' => 26, 'E' => 16, 'F' => 14, 'G' => 22, 'H' => 16, 'I' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E5AAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:I' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
