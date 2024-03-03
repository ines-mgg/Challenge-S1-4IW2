<?php

namespace App\Controller\Back;

use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Security('is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER"))')]
class InvoiceController extends AbstractController
{
    private $email;
    private $dompdf;

    public function __construct(MailerInterface $mailer)
    {
        $this->email = $mailer;
        $this->dompdf = new Dompdf();
    }

    private function pdfEmail(Invoice $invoice)
    {
        $this->dompdf->loadHtml($this->renderView('invoice/pdf.html.twig', [
            'invoice' => $invoice->getInvoice()
        ]));
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        return $this->dompdf->output();
    }

    private function sendEmail(Invoice $invoice)
    {
        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME']))
            ->to($invoice->getCustomer()->getEmail())
            ->subject($invoice->getType() === 'Devis' ? 'Voici votre devis' : 'Voici votre facture')
            ->htmlTemplate('emails/invoice.html.twig')
            ->context([
                'invoice' => $invoice,
                'url' => $this->generateUrl('app_validate', ['id' => $invoice->getId(), 'token' => $invoice->getToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ])
            ->attach($this->pdfEmail($invoice), 'invoice.pdf', 'application/pdf');
        try {
            $this->email->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
            return false;
        }
    }

    private function contactCustomer(Invoice $invoice, $choices)
    {
        switch ($choices) {
            case '1':
                $emailType = 'emails/reminder.html.twig';
                break;
            case '2':
                $emailType = 'emails/reminder2.html.twig';
                break;
            default:
                $emailType = 'emails/reminder3.html.twig';
        }

        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME']))
            ->to($invoice->getCustomer()->getEmail())
            ->subject($invoice->getType() === 'Devis' ? 'Rappel validation de votre devis' : 'Rappel paiement de votre facture')
            ->htmlTemplate($emailType)
            ->context([
                'invoice' => $invoice,
                'url' => $this->generateUrl('app_validate', ['id' => $invoice->getId(), 'token' => $invoice->getToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ])
            ->attach($this->pdfEmail($invoice), 'invoice.pdf', 'application/pdf');
        try {
            $this->email->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
            return false;
        }
    }

    private function setInvoiceStatus(Invoice $invoice, string $status, EntityManagerInterface $entityManager): void
    {
        $invoice->setStatus($status);
        $entityManager->persist($invoice);
        $entityManager->flush();
    }

    private function createInvoice(Invoice $invoice, EntityManagerInterface $entityManager)
    {
        $prestations = [];
        $totalHT = 0;
        $totalTTC = 0;

        $invoice->setStatus($invoice->getType() === 'Devis' ? 'À valider' : 'À payer');
        $invoice->setCreatedAt(new \DateTimeImmutable());

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
            $dataInvoice = [
                "date" => $invoice->getCreatedAt()->format('d/m/Y'),
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
            $invoice->setToken(bin2hex(random_bytes(32)));
            $entityManager->persist($invoice);
            $entityManager->flush();

            if ($this->sendEmail($invoice)) {
                if ($invoice->getType() === 'Devis') {
                    $this->addFlash('success', 'Le devis a bien été créé et envoyé par mail');
                } else {
                    $this->addFlash('success', 'La facture a bien été créée');
                }
            } else {
                $this->addFlash('danger', 'Une erreur est survenue lors de la création');
            }
        }
    }

    #[Route('invoice/', name: 'app_invoice_index', methods: ['GET'])]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $invoices = $invoiceRepository->findAll();
        } else {
            $invoices = $invoiceRepository->findAllInvoices($this->getUser()->getCompany()->getId());
        }
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('invoice/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $customers = $customerRepository->findAllCustomers($this->getUser()->getCompany()->getId());
        }
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && count($invoice->getInvoicePrestations()) > 0) {
            $this->createInvoice($invoice, $entityManager);
            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
            'customers' => $customers,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('invoice/{id}/delete', name: 'app_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->request->get('_token'))) {
            $invoice->setStatus('Annulé(e)');
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('invoice/{id}', name: 'app_invoice_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Invoice $invoice): Response
    {
        if ($this->isCsrfTokenValid('show' . $invoice->getId(), $request->request->get('_token'))) {
            if ($this->contactCustomer($invoice, $request->request->get('choices'))) {
                $this->addFlash('success', 'Le message a bien été envoyé');
                return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('invoice/{id}/{token}/pdf', name: 'app_invoice_pdf', methods: ['GET'])]
    public function pdf(Invoice $invoice, string $token): Response
    {
        if ($invoice->getToken() != $token) {
            return $this->redirectToRoute('app_invoice_cancelled', [], Response::HTTP_SEE_OTHER);
        }
        $pdfContent = $this->pdfEmail($invoice);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice.pdf"'
        ]);
    }

    #[Route('invoice/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
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

        $form = $this->createForm(InvoiceType::class, $invoice, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && count($newInvoice->getInvoicePrestations()) > 0) {
            $this->createInvoice($newInvoice, $entityManager);
            return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
            'connectedUser' => $this->getUser()
        ]);
    }


    #[Route('invoice/{id}/validate', name: 'app_invoice_validate', methods: ['POST'])]
    public function validate(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('validate' . $invoice->getId(), $request->request->get('_token'))) {
            $invoice->setStatus('Payée');
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('validate/confirm/', name: 'app_invoice_confirm', methods: ['GET'])]
    public function confirm(): Response
    {
        return $this->render('invoice/confirm.html.twig');
    }

    #[Route('validate/cancelled/', name: 'app_invoice_cancelled', methods: ['GET'])]
    public function cancelledInvoice(): Response
    {
        return $this->render('invoice/cancelled.html.twig');
    }

    #[Route('validate/{id}/{token}', name: 'app_validate', methods: ['GET', 'POST'])]
    public function validateInvoice(Request $request, Invoice $invoice, string $token, EntityManagerInterface $entityManager): Response
    {
        if ($invoice->getToken() != $token) {
            return $this->redirectToRoute('app_invoice_cancelled', [], Response::HTTP_SEE_OTHER);
        } elseif ($invoice->getStatus() === 'Validé' || $invoice->getStatus() === 'Annulé(e)' || $invoice->getStatus() === 'Payée') {
            return $this->redirectToRoute('app_invoice_confirm', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('validate' . $invoice->getId() . $invoice->getToken(), $request->request->get('_token'))) {
            $status = $request->request->get('choices') === '1' ? 'Validé' : 'Annulé(e)';
            $this->setInvoiceStatus($invoice, $status, $entityManager);
            return $this->redirectToRoute('app_invoice_confirm', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice/validate.html.twig', [
            'invoice' => $invoice,
            'token' => $token,
        ]);
    }
}
