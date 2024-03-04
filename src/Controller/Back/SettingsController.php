<?php


namespace App\Controller\Back;

use App\Form\UserChangePasswordFormType;
use App\Form\UserCompanyInformationsFormType;
use App\Form\UserInformationsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $informationsForm = $this->createForm(UserInformationsFormType::class, $user);
        $informationsForm->handleRequest($request);

        if ($informationsForm->isSubmitted() && $informationsForm->isValid()) {
            // flush but use the formatting function from user entity
            $entityManager->flush();

            $entityManager->refresh($user);

            $informationsForm = $this->createForm(UserInformationsFormType::class, $user);
            $this->addFlash('success', 'Vos informations ont bien été mises à jour');
        }

        $userCompany = $user->getCompany();

        $userCompanyInformationsForm = $this->createForm(UserCompanyInformationsFormType::class, $userCompany);
        $userCompanyInformationsForm->handleRequest($request);

        if ($userCompanyInformationsForm->isSubmitted() && $userCompanyInformationsForm->isValid()) {
            // flush but use the formatting function from user entity
            $entityManager->flush();

            $entityManager->refresh($userCompany);

            $userCompanyInformationsForm = $this->createForm(UserCompanyInformationsFormType::class, $userCompany);
            $this->addFlash('success', 'Vos informations d\'entreprise ont bien été mises à jour');

        }

        /*$userChangePasswordForm = $this->createForm(UserChangePasswordFormType::class);
        $userChangePasswordForm->handleRequest($request);

        if ($userChangePasswordForm->isSubmitted() && $userChangePasswordForm->isValid()) {
            $oldPassword = $userChangePasswordForm->get('oldPassword')->getData();
            $newPassword = $userChangePasswordForm->get('newPassword')->getData();


            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'Votre mot de passe actuel est incorrect');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Password changed successfully');
            }
        }*/

        return $this->render('dashboard/settings.html.twig', [
            'userInformationsForm' => $informationsForm->createView(),
            'userCompanyInformationsForm' => $userCompanyInformationsForm->createView(),
            //'userChangePasswordForm' => $userChangePasswordForm->createView(),
        ]);
    }
}