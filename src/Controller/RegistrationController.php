<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Registration\EmailStepFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route(path: '/register', name: 'app_register_')]
class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private array $steps;
    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
        $this->steps = [
            'email' => [
                'order' => 1,
                'label' => 'Email',
                'form' => EmailStepFormType::class,
                'route' => 'app_register_email',
                'next' => 'informations',
                'previous' => null,
            ],
            'informations' => [
                'order' => 2,
                'label' => 'Informations',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_informations',
                'next' => 'password',
                'previous' => 'email',
            ],
            'company' => [
                'order' => 3,
                'label' => 'Société',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_company',
                'next' => 'password',
                'previous' => 'informations',
            ],
            'confirm' => [
                'order' => 4,
                'label' => 'Confirmation',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_confirm',
                'next' => null,
                'previous' => 'company',
            ],
        ];
    }

    #[Route('/start', name: 'start')]
    public function start(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        // TODO: Voir comment Quonto gère la reprise d'inscription
        $user = new User();
        $form = $this->createForm(EmailStepFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

        }

        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["confirm"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
        ]);
    }

    /*
     * SAVE old register() for email confirmation
     *  $user = new User();
     *  $form = $this->createForm(RegistrationFormType::class, $user);
     *  $form->handleRequest($request);
     *
     *  if ($form->isSubmitted() && $form->isValid()) {
     *      // encode the plain password
     *      $user->setPassword(
     *          $userPasswordHasher->hashPassword(
     *              $user,
     *              $form->get('password')->getData()
     *          )
     *      );
     *
     *      $entityManager->persist($user);
     *      $entityManager->flush();
     *
     *      // generate a signed url and email it to the user
     *      $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
     *          (new TemplatedEmail())
     *              ->from(new Address('replace-me@facturo.com', 'Facturo Account Service'))
     *              ->to($user->getEmail())
     *              ->subject('Please Confirm your Email')
     *              ->htmlTemplate('registration/confirmation_email.html.twig')
     *      );
     *  // do anything else you need here, like send an email
     *
     *      return $this->redirectToRoute('app_login');
     *  }
     */

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
