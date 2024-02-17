<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/prestation')]
class PrestationController extends AbstractController
{
    #[Route('/', name: 'app_prestation_index', methods: ['GET'])]
    public function index(Request $request,PrestationRepository $prestationRepository): Response
    {
        $prestations = $prestationRepository->findAll();
        $prestation = new Prestation();
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);
        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestationRepository->findAll(),
            'form' => $form,

        ]);
    }

    #[Route('/new', name: 'app_prestation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prestation = new Prestation();
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prestation);
            $entityManager->flush();

            return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prestation/new.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prestation_show', methods: ['GET'])]
    public function show(Prestation $prestation): Response
    {
        return $this->render('prestation/show.html.twig', [
            'prestation' => $prestation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prestation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prestation $prestation, EntityManagerInterface $entityManager): Response
    {
        $originalPrestation = clone $prestation;
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$prestation->getInvoicePrestations()->isEmpty()) {
                $newPrestation = new Prestation();
                $newPrestation->setArchive(false);
                $newPrestation->setPrice($prestation->getPrice());
                $newPrestation->setUnite($prestation->getUnite());
                $newPrestation->setName($prestation->getName());
                $newPrestation->setTva($prestation->getTva());
                $newPrestation->setDescription($prestation->getDescription());
                $newPrestation->setCompany($prestation->getCompany());
                $entityManager->detach($prestation);
                $entityManager->persist($newPrestation);
                dump($newPrestation);
                $prestation = $entityManager->getRepository(Prestation::class)->find($prestation->getId());
                $prestation->setArchive(true);
                $entityManager->persist($prestation);
                dump($prestation);
                die();
                    $this->addFlash('warning', 'Impossible de modifier une prestation utilisée dans une facture, une nouvelle prestation a été créée avec les mêmes informations et l ancienne  a été archivée.');
            }else {
                $entityManager->persist($prestation);
                $this->addFlash('success', 'Prestation modifiée avec succès');
            }
            $entityManager->flush();


        }

        return $this->render('prestation/edit.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name: 'app_prestation_delete', methods: ['POST'])]
    public function delete(Request $request, Prestation $prestation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prestation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($prestation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
    }
}
