<?php

namespace App\Controller\Back;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\MailReply;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/contact')]
#[IsGranted('ROLE_ADMIN')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'showcase_contact', methods: ['GET', 'POST'])]
    public function index(MailerInterface $mailer,Request $request,ContactRepository $contactRepository, EntityManagerInterface $entityManager): Response
    {
        $mail = new Contact();
        $form = $this->createForm(ContactType::class, $mail);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $context = [
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData(),
                'userEmail' => $form->get('email')->getData(),
                'phoneNumber' => $form->get('phone')->getData(),
                'company' => $form->get('society_name')->getData(),
                'companySize' => $form->get('society_size')->getData(),
                'subject' => $form->get('subject')->getData(),
                'message' => $form->get('message')->getData()
            ];
            if ($this->extracted($form,$form->get('email'),$context,'emails/contact.html.twig' ,$mailer, $entityManager)) {
                $entityManager->persist($mail);
                $entityManager->flush();
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('showcase_contact');
            }else{
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du mail');
            }
        }
        return $this->render('showcase/contact.html.twig', [
            'controller_name' => 'ContactController',
            'form' => $form
        ]);
    }

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
            $context = [
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData(),
                'userEmail' => $form->get('email')->getData(),
                'phoneNumber' => $form->get('phone')->getData(),
                'company' => $form->get('society_name')->getData(),
                'subject' => $form->get('subject')->getData(),
                'message' => $form->get('message')->getData()
            ];
            if ($this->extracted($form->get('subject'),$form->get('email'),$context,'emails/contact.html.twig' ,$mailer)) {
                $entityManager->persist($contact);
                $entityManager->flush();
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
            }else{
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
            var_dump($form->getData());
            $context = [
                'subject' => $form->get('subject')->getData(),
                'message' => $form->get('message')->getData(),
            ];
            if ($this->extracted($form->get('subject')->setData('Re: '.$contact->getSubject()),$contact->getEmail(),$context,'emails/reponse.html.twig' ,$mailer )) {
                $this->addFlash('success', 'Votre message a bien été envoyé');
            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
            }else{
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

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param $mailer
     * @param $mail
     * @return string
     */
    public function extracted($subject,$to,$context,$template ,$mailer): bool
    {
        $sendEmail = (new TemplatedEmail())
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);
        try {
            $mailer->send($sendEmail);
            return true;
        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
            return false;
        }
    }

}

