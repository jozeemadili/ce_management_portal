<?php

namespace App\Http\Livewire\Components\Reports;

use App\Models\Invoice;
use Livewire\Component;

class Summary extends Component
{
    public $summary;

    public function render()
    {
        $totalInvoiced = Invoice::sum('total_invoice_amount');
        $totalPaid     = Invoice::sum('amount_paid');
        $totalUnpaid   = Invoice::sum('amount_remained');

        $this->summary = [
            'total_invoiced' => number_format($totalInvoiced, 0, '.', ','),
            'total_paid'     => number_format($totalPaid, 0, '.', ','),
            'total_unpaid'   => number_format($totalUnpaid, 0, '.', ','),
        ];

        return view('livewire.components.reports.summary');
    }
}
