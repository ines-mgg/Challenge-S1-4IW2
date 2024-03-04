<?php

namespace App\Controller\Front;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\MailReply;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;

#[Route('/', name: 'showcase_')]
class ShowcaseController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('showcase/index.html.twig', [
            'controller_name' => 'ShowcaseController',
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(MailerInterface $mailer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $mail = new Contact();
        $form = $this->createForm(ContactType::class, $mail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mail->setFirstName($form->get('firstName')->getData());
            $mail->setLastName($form->get('lastName')->getData());
            $mail->setEmail($form->get('email')->getData());
            $mail->setPhone($form->get('phone')->getData());
            $mail->setSocietySize($form->get('society_size')->getData());
            $mail->setSocietyName($form->get('society_name')->getData());
            $mail->setMessage($form->get('message')->getData());
            $mail->setSubject($form->get('subject')->getData());
            $entityManager->persist($mail);
            $entityManager->flush();
            $email = (new TemplatedEmail())
                ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME']))
                ->to($form->get('email')->getData())
                ->subject($form->get('subject')->getData())
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'firstName' => $form->get('firstName')->getData(),
                    'lastName' => $form->get('lastName')->getData(),
                    'subject' => $form->get('subject')->getData(),
                ]);
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('front_showcase_contact', [], Response::HTTP_SEE_OTHER);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du mail');
            }
        }
        return $this->render('showcase/contact.html.twig', [
            'controller_name' => 'ContactController',
            'form' => $form
        ]);
    }

    // #[Route('/tarifs', name: 'pricing')]
    // public function pricing(): Response
    // {
    //     return $this->render('showcase/tarifs.html.twig', [
    //         'controller_name' => 'ShowcaseController',
    //     ]);
    // }
}
