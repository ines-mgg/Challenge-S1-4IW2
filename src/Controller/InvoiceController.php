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

        if ($form->isSubmitted() && $form->isValid() && count($invoice->getInvoicePrestations()) > 0){

            $invoice->setStatus($invoice->getType() === 'Devis' ? 'À valider' : 'À payer');
            $invoice->setCreatedAt(new \DateTimeImmutable());

            $total = 0;
            foreach ($invoice->getInvoicePrestations() as $invoicePrestation) {
                $total += (
                    $invoicePrestation->getPrestation()->getPrice() +
                    ($invoicePrestation->getPrestation()->getPrice() *
                        ($invoicePrestation->getPrestation()->getTva() / 100))
                ) * $invoicePrestation->getQuantity();
                $invoicePrestation->setInvoice($invoice);
            }
            $invoice->setTotal($total);

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
        $oldInvoice->setStatus('Annulé(e)');
        $entityManager->persist($oldInvoice);
        $entityManager->flush();
        $newInvoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $oldInvoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($newInvoice->getInvoicePrestations()) > 0) {
            // new invoice
            $newInvoice->setCustomer($oldInvoice->getCustomer());
            $newInvoice->setType($oldInvoice->getType());
            $newInvoice->setStatus($newInvoice->getType() === 'Devis' ? 'À valider' : 'À payer');
            $newInvoice->setCreatedAt(new \DateTimeImmutable());
            $newInvoice->setClosingDate($oldInvoice->getClosingDate());
            $total = 0;
            foreach ($oldInvoice->getInvoicePrestations() as $invoicePrestation) {
                $newInvoicePrestation = clone $invoicePrestation; // clone the InvoicePrestation
                $newInvoicePrestation->setInvoice($newInvoice); // set the new Invoice as the Invoice of the new InvoicePrestation
                $newInvoice->addInvoicePrestation($newInvoicePrestation); // add the new InvoicePrestation to the new Invoice
                $total += (
                    $newInvoicePrestation->getPrestation()->getPrice() +
                    ($newInvoicePrestation->getPrestation()->getPrice() *
                        ($newInvoicePrestation->getPrestation()->getTva() / 100))
                ) * $newInvoicePrestation->getQuantity();
            }
            $newInvoice->setTotal($total);
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
