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

class ProgramRegistrationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $registrations;

    public function __construct(Collection $registrations)
    {
        $this->registrations = $registrations;
    }

    public function collection()
    {
        return $this->registrations;
    }

    public function headings(): array
    {
        return ['#', 'Reference', 'Member', 'Church', 'Program', 'Status', 'Payment', 'Amount Paid', 'Registered On'];
    }

    public function map($reg): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $reg->registration_reference,
            trim(optional($reg->member)->first_name . ' ' . optional($reg->member)->last_name),
            optional(optional($reg->member)->church)->name ?? '—',
            optional($reg->program)->name,
            ucfirst($reg->registration_status),
            ucfirst($reg->payment_status),
            $reg->amount_paid ? number_format($reg->amount_paid, 2) : '-',
            optional($reg->registered_at)->format('d M Y'),
        ];
    }

    public function title(): string
    {
        return 'Registrations';
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 16, 'C' => 24, 'D' => 22, 'E' => 26, 'F' => 12, 'G' => 12, 'H' => 14, 'I' => 14];
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
