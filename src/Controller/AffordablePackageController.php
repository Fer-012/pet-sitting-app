<?php

namespace App\Controller;

use App\Entity\AffordablePackage;
use App\Form\AffordablePackageType;
use App\Repository\AffordablePackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/affordable')]
final class AffordablePackageController extends AbstractController
{
    #[Route(name: 'app_affordable_package_index', methods: ['GET'])]
    public function index(AffordablePackageRepository $affordablePackageRepository): Response
    {
        return $this->render('affordable_package/index.html.twig', [
            'affordable_packages' => $affordablePackageRepository->findAll(),

        ]);
    }

    #[Route('/new', name: 'app_affordable_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $affordablePackage = new AffordablePackage();
        $form = $this->createForm(AffordablePackageType::class, $affordablePackage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($affordablePackage);
            $entityManager->flush();

            return $this->redirectToRoute('app_affordable_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affordable_package/new.html.twig', [
            'affordable_package' => $affordablePackage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affordable_package_show', methods: ['GET'])]
    public function show(AffordablePackage $affordablePackage): Response
    {
        return $this->render('affordable_package/show.html.twig', [
            'affordable_package' => $affordablePackage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affordable_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AffordablePackage $affordablePackage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AffordablePackageType::class, $affordablePackage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_affordable_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affordable_package/edit.html.twig', [
            'affordable_package' => $affordablePackage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affordable_package_delete', methods: ['POST'])]
    public function delete(Request $request, AffordablePackage $affordablePackage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$affordablePackage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($affordablePackage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_affordable_package_index', [], Response::HTTP_SEE_OTHER);
    }
}
