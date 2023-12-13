<?php

namespace App\Controller;

use App\Entity\OneTimeCode;
use App\Entity\User;
use App\Form\Registration\CompanyStepFormType;
use App\Form\Registration\EmailStepConfirmationFormType;
use App\Form\Registration\EmailStepFormType;
use App\Form\Registration\InformationsStepFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTimeImmutable;
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
                'route' => 'app_register_start',
                'next' => 'email_confirmation',
                'previous' => null,
            ],
            'email_confirmation' => [
                'order' => 2,
                'label' => 'Taper le code reçu par mail',
                'form' => EmailStepConfirmationFormType::class,
                'route' => 'app_register_identity-confirm',
                'next' => 'company',
                'previous' => 'email',
            ],
            'company' => [
                'order' => 3,
                'label' => 'Société',
                'form' => CompanyStepFormType::class,
                'route' => 'app_register_company',
                'next' => 'informations',
                'previous' => 'email_confirmation',
            ],
            'informations' => [
                'order' => 4,
                'label' => 'Informations',
                'form' => InformationsStepFormType::class,
                'route' => 'app_register_informations',
                'next' => 'confirm',
                'previous' => 'company',
            ],
            'confirm' => [
                'order' => 5,
                'label' => 'Confirmation',
                'form' => RegistrationFormType::class,
                'route' => 'app_register_confirm',
                'next' => null,
                'previous' => 'informations',
            ],
        ];
    }

//    public function handleStep(SessionInterface $session, Security $security, $comparingStep){
//        $step = $session->get('step');
//        // if user in session and is verified then redirect to /
//        if ($security->getUser() && $security->getUser()->isVerified()) {
//            return $this->redirectToRoute('default_index');
//        }
//
//        if ($security->getUser())
//
//
//        $step = is_null($step)
//            ? $this->steps["email"]
//            : $this->steps[$step];
//    }

    #[Route(['', '/start'], name: 'start')]
    public function start(Request $request, EntityManagerInterface $entityManager, Security $security, SessionInterface $session): Response
    {
        $user = new User();
        $form = $this->createForm($this->steps["email"]["form"], $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // if email already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $user->getEmail(),
            ]);
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
                    // Post/Redirect/Get pattern to avoid form resubmission
                    $session->getFlashBag()->add('form_errors', $errors);
                    // Redirect back to the form
                    return $this->redirectToRoute($this->steps["email"]["route"]);
                }
            } else {
                if ($user->isVerified()) {
                    $session->getFlashBag()->add('form_errors',  [
                        ["message" => "Cet email est déjà associé à un compte Facturo"]
                    ]);
                    return $this->redirectToRoute($this->steps["email"]["route"]);
                }
            }

            if ($form->isValid() || !is_null($existingUser)) {
                $session->set('step', $this->steps["email"]["next"]);
                $security->login($user);
                return $this->redirectToRoute($this->steps[$this->steps["email"]["next"]]["route"]);
            }
        }

        // Retrieve form errors from the session
        // Un nouveau tableau est créé (expliquant le [0] ?? []) car le flashbag est un tableau de tableaux
        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["email"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
        ]);
    }

    #[Route('/identity-confirm', name: 'identity-confirm')]
    public function identityConfirm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, SessionInterface $session): Response
    {
        $user = $this->getUser();

        $form = $this->createForm($this->steps["email_confirmation"]["form"]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //find latest oneTimeCode for this user
            $oneTimeCode = $entityManager->getRepository(OneTimeCode::class)->findOneBy(['user' => $user], ['created_at' => 'DESC']);
            if (!is_null($oneTimeCode) && $oneTimeCode->getCode() == $form->get('code')->getData()) {
                $isExpired = $oneTimeCode->getExpiresAt() < new DateTimeImmutable();
                if (!$isExpired) {
                    $oneTimeCode->setUsed(true);
                    $entityManager->persist($oneTimeCode);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_register_company');
                } else {
                    $session->getFlashBag()->add('form_errors',  [["message" => "Le code de confirmation a expiré"]]);
                }
            }else{
                $session->getFlashBag()->add('form_errors',  [["message" => "Le code de confirmation est invalide"]]);
            }
        } else {
            $oneTimeCode = new OneTimeCode();
            $oneTimeCode->setUser($user);
            $duration = $oneTimeCode->getExpiresAt()->diff($oneTimeCode->getCreatedAt());

            $entityManager->persist($oneTimeCode);
            $entityManager->flush();

            // TODO : Améliorer le mail
            $email = (new Email())
                ->from(new Address('noreply@facturo.fr', 'Facturo.fr'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->text('Voici votre code de confirmation : ' . $oneTimeCode->getCode(). ' Il expirera dans ' . $duration->format('%i minutes'));
            $mailer->send($email);
        }
        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["email_confirmation"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
        ]);
    }

    #[Route('/company', name: 'company')]
    public function company(Request $request): Response
    {
        $formErrors = [];

        $form = $this->createForm($this->steps["company"]["form"]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // TODO : éditer $formErrors et faire la création de l'entité company
            return $this->redirectToRoute('app_register_informations');
        }

        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["company"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors
        ]);
    }

    #[Route('/informations', name: 'informations')]
    public function informations(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // TODO : éditer $formErrors
        $formErrors = [];

        $user = new User();
        $form = $this->createForm($this->steps["informations"]["form"], $user);
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
            'formErrors' => $formErrors
        ]);
    }

    #[Route('/end', name: 'end')]
    public function end(): Response
    {
        $formErrors = [];
        return $this->render('registration/register.html.twig', [
            'step' => $this->steps["confirm"],
            'stepTotal' => count($this->steps),
            'formErrors' => $formErrors
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
     *              ->from(new Address('replace-me@facturo.fr', 'Facturo Account Service'))
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
