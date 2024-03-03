<?php

namespace App\Controller\Back;

use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app', name: 'facturo_app_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[Security('is_granted("ROLE_ADMIN") or (is_granted("ROLE_COMPTABLE"))') ]
    public function index(UserRepository $userRepository): Response
    {
        $userRepository = $userRepository->findAll();

        return $this->render('dashboard/index.html.twig', [

        ]);
    }

    #[Route('/design-guide', name: 'design-guide')]
    public function crudExampleUser(): Response
    {
        return $this->render('dashboard/design-guide.html.twig', [
        ]);
    }

    #[Route('/crud-example-product', name: 'crud-example-product')]
    public function crudExampleProduct(): Response
    {
        return $this->render('dashboard/crud-example-products.html.twig', [
        ]);
    }
}
