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
            $context = [
                'firstName' => $form->get('firstName')->getData(),
                'lastName' => $form->get('lastName')->getData(),
                'userEmail' => $form->get('email')->getData(),
                'phoneNumber' => $form->get('phone')->getData(),
                'companyName' => $form->get('society_name')->getData(),
                'companySize' => $form->get('society_size')->getData(),
                'subject' => $form->get('subject')->getData(),
                'message' => $form->get('message')->getData()
            ];
            if ($this->extracted($form,$form->get('email'),$context,'emails/contact.html.twig' ,$mailer, $entityManager)) {
                $entityManager->persist($mail);
                $entityManager->flush();
                $this->addFlash('success', 'Votre message a bien été envoyé');
                return $this->redirectToRoute('front_showcase_contact');
            }else{
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
