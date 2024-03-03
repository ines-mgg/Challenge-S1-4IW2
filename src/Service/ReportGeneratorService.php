<?php

namespace App\Service;

use App\Repository\InvoiceRepository;

class ReportGeneratorService
{
    private InvoiceRepository $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function generateRevenueReport(\DateTime $startDate, \DateTime $endDate): array
    {
        $invoices = $this->invoiceRepository->findInvoicesBetweenDates($startDate, $endDate);

        $totalRevenue = 0;
        foreach ($invoices as $invoice) {
            $totalRevenue += $invoice->getTotal();
        }

        return [
            'totalRevenue' => $totalRevenue,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'invoiceCount' => count($invoices),
        ];
    }

    public function generateRevenueReportForCompany(\DateTime $startDate, \DateTime $endDate, \App\Entity\Company $company): array
    {
        $invoices = $this->invoiceRepository->findInvoicesForCompanyBetweenDates($company, $startDate, $endDate);

        $totalRevenue = 0;
        foreach ($invoices as $invoice) {
            $totalRevenue += $invoice->getTotal();
        }

        return [
            'totalRevenue' => $totalRevenue,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'invoiceCount' => count($invoices),
        ];
    }
}