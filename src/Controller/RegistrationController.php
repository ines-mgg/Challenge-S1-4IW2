<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Registration\EmailStepConfirmationFormType;
use App\Form\Registration\EmailStepFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
                'next' => 'email_confirmation',
                'previous' => null,
            ],
            'email_confirmation' => [
                'order' => 2,
                'label' => 'Taper le code reçu par mail',
                'form' => EmailStepConfirmationFormType::class,
                'route' => 'app_register_email',
                'next' => 'informations',
                'previous' => 'email',
            ],
            'informations' => [
                'order' => 3,
                'label' => 'Informations',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_informations',
                'next' => 'informations',
                'previous' => 'company',
            ],
            'company' => [
                'order' => 4,
                'label' => 'Société',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_company',
                'next' => 'confirm',
                'previous' => 'informations',
            ],
            'confirm' => [
                'order' => 5,
                'label' => 'Confirmation',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_confirm',
                'next' => null,
                'previous' => 'company',
            ],
        ];
    }

    #[Route(['', '/start'], name: 'start')]
    public function start(Request $request, EntityManagerInterface $entityManager, Security $security, SessionInterface $session): Response
    {
        // TODO: Voir comment Qonto gère la reprise d'inscription
        $user = new User();
        $form = $this->createForm($this->steps["email"]["form"], $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // if email already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            $user = $existingUser ?? $user;

            if (is_null($existingUser)) {
                if ($form->isValid()) {
                    $entityManager->persist($user);
                    $entityManager->flush();
                } else {
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $errors[]["message"] = $error->getMessage();
                    }
                    // Save form errors in the session
                    $session->getFlashBag()->add('form_errors', $errors);
                    // Redirect back to the form
                    return $this->redirectToRoute('app_register_start');
                }
            }

            if ($form->isValid() || !is_null($existingUser)) {
                $security->login($user);
                return $this->redirectToRoute('app_register_identity-confirm');
            }
        }

        // Retrieve form errors from the session
        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["email"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
        ]);
    }

    #[Route('/identity-confirm', name: 'identity-confirm')]
    public function identityConfirm(): Response {
        dd("identity confirm");
    }

    #[Route('/informations', name: 'informations')]
    public function informations(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {


        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('a@o.ocm', 'Facturo Account Service'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["informations"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/company', name: 'company')]
    public function company(): Response
    {
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["company"],
            'stepTotal' => count($this->steps),
        ]);
    }

    #[Route('/end', name: 'end')]
    public function end(): Response
    {
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["confirm"],
            'stepTotal' => count($this->steps),
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
