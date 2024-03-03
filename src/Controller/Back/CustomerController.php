<?php

namespace App\Controller\Back;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer')]
#[Security('is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER"))')]
class CustomerController extends AbstractController
{
    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): Response
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $customers = $customerRepository->findAll();
        } else {
            $customers = $customerRepository->findAllCustomers($this->getUser()->getCompany()->getId());
        }
        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $customer->setCompany($this->getUser()->getCompany());
        $form = $this->createForm(CustomerType::class, $customer, [
            'roles' => $this->getUser()->getRoles(),
        ]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                $customer->setCompany($this->getUser()->getCompany());
            }
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_customer_show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerType::class, $customer, [
            'roles' => $this->getUser()->getRoles(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
            'connectedUser' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $customer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_index', [], Response::HTTP_SEE_OTHER);
    }
}
