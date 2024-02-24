<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app', name: 'facturo_app_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
//    #[Security('is_granted("ROLE_ACCOUNTABLE")')]
    public function index(UserRepository $userRepository): Response
    {
        $userRepository = $userRepository->findAll();

        return $this->render('dashboard/index.html.twig', [

        ]);
    }

    #[Route('/crud-example-user', name: 'crud-example-user')]
    public function crudExampleUser(): Response
    {
        return $this->render('dashboard/crud-example-user.html.twig', [
        ]);
    }

    #[Route('/crud-example-product', name: 'crud-example-product')]
    public function crudExampleProduct(): Response
    {
        return $this->render('dashboard/crud-example-products.html.twig', [
        ]);
    }
}
