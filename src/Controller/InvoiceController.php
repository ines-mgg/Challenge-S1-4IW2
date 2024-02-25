<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/', name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($invoice->getInvoicePrestations()) > 0) {

            $invoice->setStatus($invoice->getType() === 'Devis' ? 'À valider' : 'À payer');
            $invoice->setCreatedAt(new \DateTimeImmutable());

            $prestations = [];
            $totalHT = 0;
            $totalTTC = 0;
            foreach ($invoice->getInvoicePrestations() as $invoicePrestation) {
                $totalHT += $invoicePrestation->getPrestation()->getPrice() * $invoicePrestation->getQuantity();
                $totalTTC += (
                    $invoicePrestation->getPrestation()->getPrice() +
                    ($invoicePrestation->getPrestation()->getPrice() *
                        ($invoicePrestation->getPrestation()->getTva() / 100))
                ) * $invoicePrestation->getQuantity();
                $invoicePrestation->setInvoice($invoice);
                $prestations[] = [
                    'name' => $invoicePrestation->getPrestation()->getName(),
                    'priceUnit' => $invoicePrestation->getPrestation()->getPrice(),
                    'quantity' => $invoicePrestation->getQuantity(),
                    'totalHT' => $invoicePrestation->getPrestation()->getPrice() * $invoicePrestation->getQuantity(),
                    'tva' => $invoicePrestation->getPrestation()->getTva(),
                    'totalTTC' => (
                        $invoicePrestation->getPrestation()->getPrice() +
                        ($invoicePrestation->getPrestation()->getPrice() *
                            ($invoicePrestation->getPrestation()->getTva() / 100))
                    ) * $invoicePrestation->getQuantity()
                ];
            }
            $dataInvoice = [
                "date" => $invoice->getCreatedAt(),
                "company" => [
                    'logo' => $invoice->getCustomer()->getCompany()->getLogo(),
                    'name' => $invoice->getCustomer()->getCompany()->getName(),
                    'siret' => $invoice->getCustomer()->getCompany()->getSiret(),
                    'headOffice' => $invoice->getCustomer()->getCompany()->getHeadOffice()
                ],
                "customer" => [
                    "fullname" => $invoice->getCustomer()->getFullname(),
                    "email" => $invoice->getCustomer()->getEmail(),
                    "number" => $invoice->getCustomer()->getNumber(),
                    "siret" => $invoice->getCustomer()->getSiret(),
                ], 
                "prestations" => $prestations,
                "total" => [
                    "ht" => $totalHT,
                    "ttc" => $totalTTC
                ]
            ];
            $invoice->setTotal($totalTTC);
            $invoice->setInvoice($dataInvoice);
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getStatus() === 'Payée' || $invoice->getStatus() === 'Annulé(e)' || $invoice->getStatus() === 'Validé') {
            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }
        $oldInvoice = clone $invoice;
        $entityManager->detach($oldInvoice);
        $newInvoice = $oldInvoice;
        $invoice->setStatus('Annulé(e)');
        $entityManager->persist($invoice);

        $form = $this->createForm(InvoiceType::class, $newInvoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($newInvoice->getInvoicePrestations()) > 0) {
            // new invoice
            $newInvoice->setCustomer($oldInvoice->getCustomer());
            $newInvoice->setType($oldInvoice->getType());
            $newInvoice->setStatus($newInvoice->getType() === 'Devis' ? 'À valider' : 'À payer');
            $newInvoice->setCreatedAt(new \DateTimeImmutable());
            $newInvoice->setClosingDate($newInvoice->getClosingDate());
            $prestations = [];
            $totalHT = 0;
            $totalTTC = 0;
            foreach ($newInvoice->getInvoicePrestations() as $invoicePrestation) {
                $totalHT += $invoicePrestation->getPrestation()->getPrice() * $invoicePrestation->getQuantity();
                $totalTTC += (
                    $invoicePrestation->getPrestation()->getPrice() +
                    ($invoicePrestation->getPrestation()->getPrice() *
                        ($invoicePrestation->getPrestation()->getTva() / 100))
                ) * $invoicePrestation->getQuantity();
                $invoicePrestation->setInvoice($invoice);
                $prestations[] = [
                    'name' => $invoicePrestation->getPrestation()->getName(),
                    'priceUnit' => $invoicePrestation->getPrestation()->getPrice(),
                    'quantity' => $invoicePrestation->getQuantity(),
                    'totalHT' => $invoicePrestation->getPrestation()->getPrice() * $invoicePrestation->getQuantity(),
                    'tva' => $invoicePrestation->getPrestation()->getTva(),
                    'totalTTC' => (
                        $invoicePrestation->getPrestation()->getPrice() +
                        ($invoicePrestation->getPrestation()->getPrice() *
                            ($invoicePrestation->getPrestation()->getTva() / 100))
                    ) * $invoicePrestation->getQuantity()
                ];
            }
            $dataInvoice = [
                "date" => $invoice->getCreatedAt(),
                "company" => [
                    'logo' => $invoice->getCustomer()->getCompany()->getLogo(),
                    'name' => $invoice->getCustomer()->getCompany()->getName(),
                    'siret' => $invoice->getCustomer()->getCompany()->getSiret(),
                    'headOffice' => $invoice->getCustomer()->getCompany()->getHeadOffice()
                ],
                "customer" => [
                    "fullname" => $invoice->getCustomer()->getFullname(),
                    "email" => $invoice->getCustomer()->getEmail(),
                    "number" => $invoice->getCustomer()->getNumber(),
                    "siret" => $invoice->getCustomer()->getSiret(),
                ],
                "prestations" => $prestations,
                "total" => [
                    "ht" => $totalHT,
                    "ttc" => $totalTTC
                ]
            ];
            $newInvoice->setTotal($totalTTC);
            $newInvoice->setInvoice($dataInvoice);
            $entityManager->persist($newInvoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->request->get('_token'))) {
            $invoice->setStatus('Annulé(e)');
            // $entityManager->remove($invoice);
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}
