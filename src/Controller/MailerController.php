<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class MailerController extends AbstractController
{
    #[Route('/mailer', name: 'app_mailer')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->from('facturoproject@proton.me') // Sender
            ->to('estelle272001@gmail.com')// Recipient
            ->subject('Devis') // Subject
            ->htmlTemplate('emails/signup.html.twig')
            ->text('Sending emails is fun again!') // Content
            ->html('<p>This is you devis !</p>'); // Content

        try {
            $mailer->send($email);
            return $this->render('mailer/index.html.twig', [
                'controller_name' => 'Test ok',
            ]);
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }
}
