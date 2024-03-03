<?php

namespace App\Controller;

use App\Security\SiretVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    private $siretVerifier;

    public function __construct(SiretVerifier $siretVerifier)
    {
        $this->siretVerifier = $siretVerifier;
    }

    #[Route('/test-siret/{siret}', name: 'test_siret')]
    public function testSiret(Request $request, string $siret): JsonResponse
    {
        $details = $request->query->get('details', false);

        $siretData = $this->siretVerifier->fetchSiretData($siret);
        if (!$siretData) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Aucune donnée trouvée pour le SIRET fourni.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($details) {
            $info = $this->siretVerifier->extractSiretInfo($siretData);
            return new JsonResponse([
                'success' => true,
                'data' => $info,
            ]);
        } else {
            return new JsonResponse([
                'success' => true,
                'data' => $siretData,
            ]);
        }
    }
}