<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\MailerController;
use Symfony\Component\Mailer\MailerInterface;


class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
    public function index(MailerInterface $mailer): Response
    {

        $data = [
            'title' => "Welcome to our PDF Test"
        ];
//        $form = $this->createForm(ContactType::class, $data);
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
            $html = $this->renderView('pdf_generator/index.html.twig', [
            'title' => "Welcome to our PDF Test",
            'controller_name' => 'PdfGeneratorController NOn',
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $email = (new Email())
            ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME'])) // Sender
            ->to('estelle272001@gmail.com')// Recipient
            ->subject('Devis') // Subject
            ->text('Sending Devis is fun again!') // Content
            ->html('<p>This is you devis ok !</p>') // Content
            ->attach($dompdf->output(), 'mypdf.pdf', 'application/pdf');

        try {
            $mailer->send($email);
        return $this->render('pdf_generator/index.html.twig', [
            'controller_name' => 'PdfGeneratorController',
            'title' => "Welcome to our PDF Test",
        ]);
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }
        return $this->render('pdf_generator/index.html.twig', [
            'title' => "Welcome to our PDF Test",
        ]);
//     return new Response('Email sent successfully');
    }

}
