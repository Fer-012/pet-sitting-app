<?php

namespace App\Controller;

use App\Entity\Sitter;
use App\Form\SitterType;
use App\Repository\SitterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sitter')]
final class SitterController extends AbstractController
{
    #[Route(name: 'app_sitter_index', methods: ['GET'])]
    public function index(SitterRepository $sitterRepository): Response
    {
        return $this->render('admin/sitter/index.html.twig', [
            'sitters' => $sitterRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sitter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sitter = new Sitter();
        $form = $this->createForm(SitterType::class, $sitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sitter);
            $entityManager->flush();

            return $this->redirectToRoute('app_sitter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/sitter/new.html.twig', [
            'sitter' => $sitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sitter_show', methods: ['GET'])]
    public function show(Sitter $sitter): Response
    {
        return $this->render('sitter/show.html.twig', [
            'sitter' => $sitter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sitter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sitter $sitter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SitterType::class, $sitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sitter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/sitter/edit.html.twig', [
            'sitter' => $sitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sitter_delete', methods: ['POST'])]
    public function delete(Request $request, Sitter $sitter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sitter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sitter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sitter_index', [], Response::HTTP_SEE_OTHER);
    }
}
