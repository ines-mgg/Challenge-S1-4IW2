<?php

namespace App\Controller\Back;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\MailReply;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact')]
#[Security('is_granted("ROLE_ADMIN")')]
class ContactController extends AbstractController
{
    #[Route('/all', name: 'app_contact_index', methods: ['GET', 'POST'])]
    public function all(ContactRepository $contactRepository): Response
    {
        return $this->render('contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(MailReply::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME']))
                ->to($form->get('email'))
                ->subject($form->get('subject'))
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'firstName' => $form->get('firstName')->getData(),
                    'lastName' => $form->get('lastName')->getData(),
                    'userEmail' => $form->get('email')->getData(),
                    'phoneNumber' => $form->get('phone')->getData(),
                    'company' => $form->get('society_name')->getData(),
                    'subject' => $form->get('subject')->getData(),
                    'message' => $form->get('message')->getData()
                ])
            ;
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du mail');
            }
        }
        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(MailerInterface $mailer,Request $request, Contact $contact, EntityManagerInterface $entityManager ): Response
    {
        $form = $this->createForm(MailReply::class);
        $form->get('subject')->setData('Re: '.$contact->getSubject());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from(new Address($_ENV['MAILER_NOREPLY_EMAIL_ADDRESS'], $_ENV['MAILER_NOREPLY_EMAIL_NAME']))
                ->to($form->get('email')->getData())
                ->subject($form->get('subject')->setData('Re: '.$contact->getSubject()))
                ->htmlTemplate('emails/contactReply.html.twig')
                ->context([
                    'subject' => $form->get('subject')->getData(),
                    'message' => $form->get('message')->getData(),
                ]);
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
            } catch (TransportExceptionInterface $e) {
                 $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du mail');
            }
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }

}

