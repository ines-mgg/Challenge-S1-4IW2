<?php


namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings_index')]
    public function index(): Response
    {
        return $this->render('dashboard/settings.html.twig');
    }
}