<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdf;
    public $accountNumber;
    public $startDate;
    public $endDate;
    public $customerName;

    public function __construct($pdf, $accountNumber, $startDate, $endDate, $customerName)
    {
        $this->pdf = $pdf;
        $this->accountNumber = $accountNumber;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->customerName = $customerName;
        
    }

    public function build()
    {
        return $this->subject("Monthly Statement for Account {$this->accountNumber}")
            ->view('emails.statement')
            ->with([
                'accountNumber' => $this->accountNumber,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'customerName' => $this->customerName
            ])
            ->attachData(
                $this->pdf,
                "Statement_{$this->accountNumber}_{$this->startDate}_to_{$this->endDate}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
