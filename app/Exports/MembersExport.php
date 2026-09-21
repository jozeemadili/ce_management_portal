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

class MembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $members;

    public function __construct(Collection $members)
    {
        $this->members = $members;
    }

    public function collection()
    {
        return $this->members;
    }

    public function headings(): array
    {
        return [
            '#',
            'Full Name',
            'Phone',
            'Email',
            'Church',
            'Designation(s)',
            'Cell Group(s)',
            'Department(s)',
            'Foundation Classes',
            'Baptism Status',
            'Marriage Status',
            'Joined On',
        ];
    }

    public function map($member): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            trim($member->first_name . ' ' . $member->last_name),
            $member->phone,
            $member->email,
            optional($member->church)->name,
            $member->member_roles->pluck('member_designation.name')->filter()->implode(', '),
            $member->cell_groups->pluck('name')->implode(', '),
            $member->departments->pluck('name')->implode(', '),
            strtoupper($member->foundation_clases ?? 'N/A'),
            strtoupper($member->baptism_status ?? 'N/A'),
            strtoupper($member->marriage_status ?? 'N/A'),
            optional($member->created_at)->format('d M Y'),
        ];
    }

    public function title(): string
    {
        return 'Members';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 26,
            'C' => 16,
            'D' => 26,
            'E' => 26,
            'F' => 24,
            'G' => 22,
            'H' => 22,
            'I' => 16,
            'J' => 14,
            'K' => 14,
            'L' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
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

        $sheet->getStyle('A1:L1')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:L' . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle('A1:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I1:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
