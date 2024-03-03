<?php

namespace App\Controller;

use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ReportGeneratorService;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ReportController extends AbstractController
{
    private ReportGeneratorService $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    /**
     * @Route("/report", name="report")
     */
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $company = $user->getCompany();
        if (!$company) {
            throw new \Exception('L\'utilisateur doit être associé à une entreprise pour accéder aux rapports.');
        }

        $report = null;
        $invoices = [];

        $form = $this->createFormBuilder()
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get startDate and endDate from the form data
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();

            $report = $this->reportGeneratorService->generateRevenueReportForCompany($startDate, $endDate, $company);
            $invoices = $invoiceRepository->findInvoicesForCompanyBetweenDates($company, $startDate, $endDate);
        }

        return $this->render('report/index.html.twig', [
            'form' => $form->createView(),
            'report' => $report,
            'invoices' => $invoices,
        ]);
    }
}