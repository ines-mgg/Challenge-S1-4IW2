<?php

namespace App\Controller\Back;

use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prestation')]
#[Security('is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER"))')]
class PrestationController extends AbstractController
{
    #[Route('', name: 'app_prestation_index', methods: ['GET'])]
    public function index(Request $request, PrestationRepository $prestationRepository): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $prestations = $prestationRepository->findAll();
        } else {
            $prestations = $prestationRepository->findAllPrestations($this->getUser()->getCompany()->getId());
        }
        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestationRepository->findAll(),
            'prestations' => $prestations,
        ]);
    }

    #[Route('/new', name: 'app_prestation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prestation = new Prestation();
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $prestation->setCompany($this->getUser()->getCompany());
            $entityManager->persist($prestation);
            $entityManager->flush();
            $this->addFlash('success', 'Prestation ajoutée avec succès');
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
        if (count($prestation->getInvoicePrestations()) > 0) {
            $newPrestation = clone $prestation;
            $entityManager->detach($newPrestation);
            $prestationToPersist = $newPrestation;
            $prestationToPersist->setArchive(false);
            $prestation->setArchive(true);
            $entityManager->persist($prestation);
            $this->addFlash('warning', 'Attention, cette prestation est déjà liée à une facture, vous ne pouvez pas la modifier');
        } else {
            $prestationToPersist = $prestation;
        }
        $form = $this->createForm(PrestationType::class, $prestationToPersist);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prestationToPersist);
            $entityManager->flush();
            $this->addFlash('success', 'Prestation modifiée avec succès');
        }
        return $this->render('prestation/edit.html.twig', [
            'prestation' => $prestationToPersist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prestation_delete', methods: ['POST'])]
    public function delete(Request $request, Prestation $prestation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $prestation->getId(), $request->request->get('_token'))) {
            $invoicePrestations = $prestation->getInvoicePrestations();
            foreach ($invoicePrestations as $invoicePrestation) {
                $entityManager->remove($invoicePrestation);
            }
            $entityManager->remove($prestation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
    }
}
