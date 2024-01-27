<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // if logged in, redirect to homepage
        $user = $this->getUser();
        if ($user && $user->isVerified()) {
            return $this->redirectToRoute('showcase_index');
        }

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        $lastEmail = $authenticationUtils->getLastUsername();
        $errors = $authenticationUtils->getLastAuthenticationError();


        //find the csrf token id

        return $this->render('registration/login.html.twig', [
            'loginForm' => $form->createView(),
            'lastEmail' => $lastEmail,
            'errors' => $errors,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
