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

class PledgeCampaignsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $campaigns;

    public function __construct(Collection $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    public function collection()
    {
        return $this->campaigns;
    }

    public function headings(): array
    {
        return [
            '#', 'Campaign', 'Church', 'Status', 'Target', 'Total Pledged',
            'Total Fulfilled', 'Outstanding', 'Pledgers', 'Completion %',
        ];
    }

    public function map($campaign): array
    {
        static $row = 0;
        $row++;

        $pledged = $campaign->totalPledged();
        $fulfilled = $campaign->totalFulfilled();

        return [
            $row,
            $campaign->name,
            $campaign->scope === 'global' ? 'Global' : optional($campaign->church)->name,
            ucfirst($campaign->status),
            number_format($campaign->target_amount, 2),
            number_format($pledged, 2),
            number_format($fulfilled, 2),
            number_format(max(0, $pledged - $fulfilled), 2),
            $campaign->pledgersCount(),
            $campaign->progressPercent() . '%',
        ];
    }

    public function title(): string
    {
        return 'Pledge Campaigns';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 30, 'C' => 22, 'D' => 12, 'E' => 18,
            'F' => 18, 'G' => 18, 'H' => 18, 'I' => 10, 'J' => 14,
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
        $sheet->getStyle('D1:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I1:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
