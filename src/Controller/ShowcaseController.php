<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'showcase_')]
class ShowcaseController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('showcase/index.html.twig', [
            'controller_name' => 'ShowcaseController',
        ]);
    }

    #[Route('/', name: 'pricing')]
    public function pricing(): Response
    {
        return $this->render('showcase/index.html.twig', [
            'controller_name' => 'ShowcaseController',
        ]);
    }

    #[Route('/', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('showcase/index.html.twig', [
            'controller_name' => 'ShowcaseController',
        ]);
    }
}
