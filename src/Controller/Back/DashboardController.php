<?php

namespace App\Controller\Back;

use App\Repository\InvoiceRepository;
use App\Service\ReportGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app', name: 'facturo_app_')]
class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'dashboard')]
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $company = $user->getCompany();
        if (!$company) {
            throw new \Exception('L\'utilisateur doit être associé à une entreprise pour accéder aux rapports.');
        }

        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/crud-example-user', name: 'crud-example-user')]
    public function crudExampleUser(): Response
    {
        return $this->render('dashboard/crud-example-user.html.twig');
    }

    #[Route('/crud-example-product', name: 'crud-example-product')]
    public function crudExampleProduct(): Response
    {
        return $this->render('dashboard/crud-example-products.html.twig');
    }
}
