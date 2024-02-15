<?php

namespace App\Controller;

use App\Entity\OneTimeCode;
use App\Entity\User;
use App\Form\Registration\CompanyStepFormType;
use App\Form\Registration\ConfirmStepFormType;
use App\Form\Registration\EmailStepConfirmationFormType;
use App\Form\Registration\EmailStepFormType;
use App\Form\Registration\InformationsStepFormType;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\SiretVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/register', name: self::ROUTE_PREFIX)]
class RegistrationController extends AbstractController
{
    private const ROUTE_PREFIX = 'app_register_';
    private const SESSION_AUTH_KEY = 'registration_auth';
    // Registration auth timeout
    private const SESSION_AUTH_TIMEOUT = '+3600 seconds';
    private EmailVerifier $emailVerifier;
    private array $steps;
    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
        $this->steps = [
            'email' => [
                'order' => 1,
                'label' => 'E-mail',
                'form' => EmailStepFormType::class,
                'route' => self::ROUTE_PREFIX.'start',
                'previous' => null,
                'next' => 'email_confirmation',
            ],
            'email_confirmation' => [
                'order' => 2,
                'label' => 'Taper le code reçu par mail',
                'form' => EmailStepConfirmationFormType::class,
                'route' => self::ROUTE_PREFIX.'identity-confirm',
                'previous' => 'email',
                'next' => 'company',
            ],
            'company' => [
                'order' => 3,
                'label' => 'Société',
                'form' => CompanyStepFormType::class,
                'route' => self::ROUTE_PREFIX.'company',
                'previous' => 'email_confirmation',
                'next' => 'informations',
            ],
            'informations' => [
                'order' => 4,
                'label' => 'Informations',
                'form' => InformationsStepFormType::class,
                'route' => self::ROUTE_PREFIX.'informations',
                'previous' => 'company',
                'next' => 'confirm',
            ],
            'confirm' => [
                'order' => 5,
                'label' => 'Confirmation',
                'form' => ConfirmStepFormType::class,
                'route' => self::ROUTE_PREFIX.'end',
                'previous' => 'informations',
                'next' => null,
            ],
        ];
    }

    // TODO : Voir note

    /**
     * Permet de vérifier si l'utilisateur est bien authentifié pour accéder à la page
     * @param Request $request
     * @param SessionInterface $session
     * @param Security $security
     * @return RedirectResponse|null Une réponse null équivaut à un accès autorisé
     */
    function handleSecurity(Request $request, SessionInterface $session, Security $security): ?RedirectResponse
    {
        $completeRouteName = $request->attributes->get('_route');
        $routeName = substr($completeRouteName, strlen(self::ROUTE_PREFIX));

        $loggedUser = $security->getUser();
        if ($loggedUser) {
            if ($loggedUser->isVerified()) {
                // TODO: redirect to facturo backoffice
                return $this->redirectToRoute('app_dashboard');
            }

            $registrationStatus = $session->get(self::SESSION_AUTH_KEY);
            if (is_null($registrationStatus)) {
                if (in_array($routeName, ["start", "identity-confirm"])) {
                    return null;
                }

                $security->logout(false);
                $session->getFlashBag()->add('form_errors',  [
                    ["message" => "Vous n'êtes pas autorisé à accéder à cette page"],
                    ["message" => "Nous avons sauvegardé votre état d'avancement pour que vous puissiez reprendre plus tard"]
                ]);
                return $this->redirectToRoute("app_register_start");
            }

            if ($registrationStatus["expires_at"] < new DateTimeImmutable()) {
                $security->logout(false);
                $session->getFlashBag()->add('form_errors',  [
                    ["message" => "Votre session d'inscription a expiré"],
                    ["message" => "Nous avons sauvegardé votre état d'avancement pour que vous puissiez reprendre plus tard"]
                ]);
                $session->remove(self::SESSION_AUTH_KEY);
                return $this->redirectToRoute("app_register_start");
            }

            if ($registrationStatus && !in_array($routeName, ["company", "informations", "end"])) {
                return $this->redirectToRoute('app_register_company');
            }
        } else {
            if ($routeName === "start") {
                return null;
            }
            return $this->redirectToRoute("app_register_start");
        }
        return null;
    }


    #[Route(['', '/start'], name: 'start')]
    public function start(Request $request, EntityManagerInterface $entityManager, Security $security, SessionInterface $session): Response
    {
        $handleSecurityResponse = $this->handleSecurity($request, $session, $security);
        if ($handleSecurityResponse instanceof RedirectResponse) {
            return $handleSecurityResponse;
        }

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
        return $this->render('registration/steps/email.html.twig', [
            'step' => $this->steps["email"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
        ]);
    }

    #[Route('/identity-confirm', name: 'identity-confirm')]
    public function identityConfirm(Request $request, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer, SessionInterface $session): Response
    {
        $user = $this->getUser();

        $form = $this->createForm($this->steps["email_confirmation"]["form"]);
        $form->handleRequest($request);

        $handleSecurityResponse = $this->handleSecurity($request, $session, $security);
        if ($handleSecurityResponse instanceof RedirectResponse) {
            return $handleSecurityResponse;
        }
        
        if ($form->isSubmitted()) {
            //find latest oneTimeCode for this user
            $oneTimeCode = $entityManager->getRepository(OneTimeCode::class)->findOneBy(['user' => $user], ['id' => 'DESC']);
            if (!is_null($oneTimeCode) && $oneTimeCode->getCode() == $form->get('code')->getData()) {
                $isExpired = $oneTimeCode->getExpiresAt() < new DateTimeImmutable();
                if (!$isExpired) {
                    $oneTimeCode->setUsed(true);
                    $entityManager->persist($oneTimeCode);
                    $entityManager->flush();

                    $session->set(self::SESSION_AUTH_KEY, [
                        "expires_at" => new DateTimeImmutable(self::SESSION_AUTH_TIMEOUT)
                    ]);

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
        return $this->render('registration/steps/email_confirmation.html.twig', [
            'step' => $this->steps["email_confirmation"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
        ]);
    }

    #[Route('/company', name: 'company')]
    public function company(Request $request, SessionInterface $session, Security $security): Response
    {
        $handleSecurityResponse = $this->handleSecurity($request, $session, $security);
        if ($handleSecurityResponse instanceof RedirectResponse) {
            return $handleSecurityResponse;
        }

        $form = $this->createForm($this->steps["company"]["form"]);
        $form->handleRequest($request);
        //	85296013700010

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $siretVerifier = new SiretVerifier(
                    HttpClient::create(),
                    $_ENV['INSEE_API_TOKEN']
                );

                $errors = [];

                $siretData = $siretVerifier->fetchSiretData($form->get('company')->getData());
                if (is_null($siretData)) {
                    $errors[]["message"] = "Aucune entreprise active n'a été trouvée avec ce SIRET";
                } else {
                    $siretInfo = $siretVerifier->extractSiretInfo($siretData);
                    if (is_null($siretInfo)) {
                        $errors[]["message"] = "Aucune entreprise active n'a été trouvée avec ce SIRET";
                    }
                }

                if (count($errors) > 0) {
                    // Save form errors in the session
                    // Post/Redirect/Get pattern to avoid form resubmission
                    $session->getFlashBag()->add('form_errors', $errors);
                    // Redirect back to the form
                    return $this->redirectToRoute($this->steps["company"]["route"]);
                }
                // CREER COMPANY

                return $this->redirectToRoute($this->steps[$this->steps["company"]["next"]]["route"]);
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[]["message"] = $error->getMessage();
                }
                // Save form errors in the session
                // Post/Redirect/Get pattern to avoid form resubmission
                $session->getFlashBag()->add('form_errors', $errors);
                // Redirect back to the form
                return $this->redirectToRoute($this->steps["company"]["route"]);
            }
        }

        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/steps/company.html.twig', [
            'step' => $this->steps["company"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors,
            'nextStepRoute' => $this->steps[$this->steps["company"]["next"]]["route"],
            'company' => [
                'siret' => '85296013700010',
                'name' => 'Facturo',
                'address' => '1 rue de la paix',
            ]
        ]);
    }

    #[Route('/informations', name: 'informations')]
    public function informations(Request $request, SessionInterface $session, Security $security, EntityManagerInterface $entityManager): Response
    {
        $handleSecurityResponse = $this->handleSecurity($request, $session, $security);
        if ($handleSecurityResponse instanceof RedirectResponse) {
            return $handleSecurityResponse;
        }
        $user = $this->getUser();

        // TODO : RETIRER false (pour l'instant, on peut passer à l'étape suivante sans avoir de company)
        //  -> MANQUE API SIREN
        if (!$user->getCompany() && false) {
            return $this->redirectToRoute($this->steps["company"]["route"]);
        }

        $form = $this->createForm($this->steps["informations"]["form"], $user);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute($this->steps[$this->steps["informations"]["next"]]["route"]);
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[]["message"] = $error->getMessage();
                }
                // Save form errors in the session
                // Post/Redirect/Get pattern to avoid form resubmission
                $session->getFlashBag()->add('form_errors', $errors);
                // Redirect back to the form
                return $this->redirectToRoute($this->steps["informations"]["route"]);
            }
        }

        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/steps/informations.html.twig', [
            'step' => $this->steps["informations"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
            'formErrors' => $formErrors
        ]);
    }

    #[Route('/end', name: 'end')]
    public function end(Request $request, SessionInterface $session, Security $security, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $handleSecurityResponse = $this->handleSecurity($request, $session, $security);
        if ($handleSecurityResponse instanceof RedirectResponse) {
            return $handleSecurityResponse;
        }

        $user = $this->getUser();

        // TODO : RETIRER false (pour l'instant, on peut passer à l'étape suivante sans avoir de company)
        //  -> MANQUE API SIREN
        if (false) {
            if (!$user->getCompany()) {
                return $this->redirectToRoute($this->steps["company"]["route"]);
            }
        }

        if (false) {
            if ($user->getFirstname() === null || $user->getLastname() === null) {
                return $this->redirectToRoute($this->steps["informations"]["route"]);
            }
        }

        // TODO : éditer $formErrors pour afficher les erreurs de validation
        $formErrors = [];
        $form = $this->createForm($this->steps["confirm"]["form"]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // check if password and passwordConfirm are the same
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $errors[]["message"] = $error->getMessage();
                }
                // Save form errors in the session
                // Post/Redirect/Get pattern to avoid form resubmission
                $session->getFlashBag()->add('form_errors', $errors);
                return $this->redirectToRoute($this->steps["confirm"]["route"]);
            } else {
                $user = $this->getUser();
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $user->setIsVerified(true);
                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email

                $session->remove(self::SESSION_AUTH_KEY);
                return $this->redirectToRoute('app_login');
            }
        }


        $formErrors = $session->getFlashBag()->get('form_errors')[0] ?? [];
        return $this->render('registration/steps/end.html.twig', [
            'step' => $this->steps["confirm"],
            'stepTotal' => count($this->steps),
            'registrationForm' => $form->createView(),
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

//    #[Route('/verify/email', name: 'verify_email')]
//    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
//    {
//        $id = $request->query->get('id');
//
//        if (null === $id) {
//            return $this->redirectToRoute('app_register');
//        }
//
//        $user = $userRepository->find($id);
//
//        if (null === $user) {
//            return $this->redirectToRoute('app_register');
//        }
//
//        // validate email confirmation link, sets User::isVerified=true and persists
//        try {
//            $this->emailVerifier->handleEmailConfirmation($request, $user);
//        } catch (VerifyEmailExceptionInterface $exception) {
//            $this->addFlash('verify_email_error', $exception->getReason());
//
//            return $this->redirectToRoute('app_register');
//        }
//
//        // @TODO Change the redirect on success and handle or remove the flash message in your templates
//        $this->addFlash('success', 'Your email address has been verified.');
//
//        return $this->redirectToRoute('app_register');
//    }
}
