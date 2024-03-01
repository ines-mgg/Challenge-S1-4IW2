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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use dompdf\dompdf;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('validate')]
class InvoiceValidateController extends AbstractController
{
    public function setInvoiceStatus(Invoice $invoice, string $status, EntityManagerInterface $entityManager): void
    {
        $invoice->setStatus($status);
        $entityManager->persist($invoice);
        $entityManager->flush();
    }

    #[Route('/', name: 'app_invoice_confirm', methods: ['GET'])]
    public function confirm(): Response
    {
        return $this->render('invoice/confirm.html.twig');
    }

    #[Route('/{id}', name: 'app_validate', methods: ['GET', 'POST'])]
    public function validate(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getStatus() != 'Validé' && $invoice->getStatus() != 'Annulé(e)') {
            return $this->redirectToRoute('app_invoice_confirm', [], Response::HTTP_SEE_OTHER);
        }

        if (
            $this->isCsrfTokenValid('validate' . $invoice->getId(), $request->request->get('_token'))
            && ($request->request->get('choices') == 1 || $request->request->get('choices') == 2)
        ) {
            $status = $request->request->get('choices') == '1' ? 'Validé' : 'Annulé(e)';
            $this->setInvoiceStatus($invoice, $status, $entityManager);
            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('invoice/validate.html.twig', [
            'invoice' => $invoice,
        ]);
    }
}
