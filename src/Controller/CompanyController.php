<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/company')]
class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository): Response
    {
        return $this->render('company/index.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company, InvoiceRepository $invoicesRepository, UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy(['company' => $company->getId()]);
        $invoices = $invoicesRepository->findAllInvoices($company->getId());
        return $this->render('company/show.html.twig', [
            'company' => $company,
            'invoices' => $invoices,
            'users' => $users
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $users = $company->getUsers();
            foreach ($users as $user) {
                $entityManager->remove($user);
            }
            $prestations = $company->getPrestations();
            foreach ($prestations as $prestation) {
                $invoicePrestations = $prestation->getInvoicePrestations();

                foreach ($invoicePrestations as $invoicePrestation) {
                    $entityManager->remove($invoicePrestation);
                }

                $entityManager->remove($prestation);
            }
            $customers = $company->getCustomers();
            foreach ($customers as $customer) {
                $invoices = $customer->getInvoices();
                foreach ($invoices as $invoice) {
                    $entityManager->remove($invoice);
                }
                $entityManager->remove($customer);
            }
            $entityManager->remove($company);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
    }
}
